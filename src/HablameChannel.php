<?php

namespace Sideso\Hablame;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Sideso\Hablame\Exceptions\CouldNotSendNotification;
use Sideso\Hablame\Hablame;
use Sideso\SMS\Events\SmsSent;
use Sideso\SMS\Message;

class HablameChannel
{
    /**
     * The Hablame client instance.
     *
     * @var Hablame
     */
    protected $hablame;

    /**
     * @var int
     * The message body content count should be no longer than 6 message parts(918).
     */
    protected $character_limit_count = 918;

    public function __construct(Hablame $hablame)
    {
        $this->hablame = $hablame;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @throws \Sideso\Sideso\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toHablame')) {
            $message = $notification->toHablame($notifiable);
        } elseif (method_exists($notification, 'toSMS')) {
            $message = $notification->toSMS($notifiable);
        } else {
            throw CouldNotSendNotification::invalidMessageObject($notification);
        }

        if (is_string($message)) {
            $message = new Message($message);
        }

        if (! $message instanceof Message) {
            throw CouldNotSendNotification::invalidMessageObject($message);
        }

        if (mb_strlen($message->content) > $this->character_limit_count) {
            throw CouldNotSendNotification::contentLengthLimitExceeded($this->character_limit_count);
        }

        $message->to = $notifiable->routeNotificationFor('hablame', $notification);

        if (! $message->to) {
            $message->to = $notifiable->routeNotificationFor('sms', $notification);
        }

        $sent = $this->sendMessage($message);

        if ($message->callback && is_callable($message->callback)) {
            call_user_func($message->callback, $notifiable, $notification, $message);
        }

        return $sent;
    }

    public function sendTextToNumber(string $text, string $number)
    {
        $message = new Message($text);
        $message->to = $number;

        return $this->sendMessage($message);
    }

    public function bulkSend($bulk)
    {
        $response = $this->hablame->sendBulkMessage(
            bulk: $bulk,
        );

        if ($response['status'] == '1x000') {
            foreach ($bulk as $item) {
                $message = new Message($item['sms']);
                $message->to = $item['numero'];
                $message->sent = true;
                $message->provider_msg_id = $response['loteId'];
                $message->provider('hablame');
                SmsSent::dispatch($message);
            }
        } else {
            Log::error('Hablame SMS Error: '.$response['status'], [$response, $bulk]);
        }

        return $response;
    }

    public function sendMessage(Message $message)
    {
        if (! $message->to) {
            return;
        }

        $message->provider('hablame');

        $response = $this->hablame->sendMessage(
            toNumber: $message->to,
            message: trim($message->content),
            priority: $message->priority,
            flash: $message->flash,
            sc: $message->source_code,
            request_dlvr_rcpt: $message->request_dlvr_rcpt,
            sendDate: $message->send_date
        );

        if ($response['status'] == '1x000') {
            $message->sent = true;
            $message->provider_msg_id = $response['smsId'];
        } else {
            Log::error('Hablame SMS Error: '.$response['status'], [$message, $response]);
        }

        SmsSent::dispatch($message);

        return $response;
    }
}
