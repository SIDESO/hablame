<?php

namespace Sideso\Hablame;

use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Sideso\Hablame\HablameMessage;
use Sideso\Hablame\Exceptions\CouldNotSendNotification;


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
        $to = $notifiable->routeNotificationFor('hablame', $notification);

        if(!$to){
            $to = $notifiable->routeNotificationFor('sms', $notification);
        }

        if (!$to) {
            return;
        }

        $message = $notification->toHablame($notifiable);

        if (is_string($message)) {
            $message = new HablameMessage($message);
        }

        if (mb_strlen($message->content) > $this->character_limit_count) {
            throw CouldNotSendNotification::contentLengthLimitExceeded($this->character_limit_count);
        }

        return $this->hablame->sendMessage(
            toNumber: $to,
            message: trim($message->content),
            priority: $message->priority,
            flash: $message->flash, 
            sc: $message->source_code, 
            request_dlvr_rcpt: $message->request_dlvr_rcpt, 
            sendDate: $message->send_date
        );
    }
}
