<?php

namespace Sideso\Hablame;


use Exception;
use Illuminate\Support\Arr;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Sideso\Hablame\Exceptions\CouldNotSendNotification;

class Hablame
{
    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @var string Hablame API URL.
     */
    protected array $apiUrl = [
        'priority' => 'https://api103.hablame.co/api/sms/v3/send/priority',
        'marketing' => 'https://api103.hablame.co/api/sms/v3/send/marketing',
    ];

    /**
     * @var null|string Hablame Account Number.
     */
    protected $account;

    /**
     * @var null|string Hablame API Key.
     */
    protected $apiKey;

    /**
     * @var null|string Hablame token.
     */
    protected $token;

    /**
     * @var null|string Hablame Source Code.
     */
    protected $source_code;

    /**
     * @param  string  $apiKey
     * @param  string  $token
     * @param  string  $account
     * @param  string  $source_code
     */
    public function __construct(
        string $account = null,
        string $apiKey = null, 
        string $token = null,
        HttpClient $httpClient,
        string $sourceCode = '')
    {
        $this->account = $account;
        $this->apiKey = $apiKey;
        $this->token = $token;
        $this->client = $httpClient;
        $this->source_code = $sourceCode;
    }

    /**
     * Get API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Set API key.
     *
     * @param  string  $apiKey
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Send text message.
     *
     * <code>
     * $params = [
     *      'toNumber'           => '',
     *      'sms'                => '',
     *      'flash'              => '',
     *      'sc'                 => '',
     *      'request_dlvr_rcpt'  => '',
     *      'sendDate'           => '',
     * ];
     * </code>
     *
     * @link https://developer.hablame.co/docs/api-sms/f3b34bf7929dd-envio-de-un-sms-prioritario
     *
     * @param  array  $params
     */
    public function sendMessage(
        string $toNumber, 
        string $message, 
        bool $priority = false, 
        bool $flash = false, 
        string $sc = '', 
        bool $request_dlvr_rcpt = false, 
        ?Carbon $sendDate = null)
    {
        $this->priority = $priority;

        $params = [
            'toNumber' => $toNumber,
            'sms' => $message,
            'flash' => $flash ? "1" : "0",
            'sc' => $sc != ''  ? $sc : $this->source_code,
            'request_dlvr_rcpt' => $request_dlvr_rcpt ? "1" : "0",
        ];

        if($sendDate && $priority) {
            $params['sendDate'] = $sendDate->timestamp;
        }

        return $this->sendRequest($params);
    }
    

    private function getEndpoint(): string
    {
        return $this->priority ? $this->apiUrl['priority'] : $this->apiUrl['marketing'];
    }

    private function sendRequest(array $params)
    {
        if (empty($this->account)) {
            throw CouldNotSendNotification::accountNotProvided();
        }
        if (empty($this->apiKey)) {
            throw CouldNotSendNotification::apiKeyNotProvided();
        }
        if (empty($this->token)) {
            throw CouldNotSendNotification::tokenNotProvided();
        }

        try {
            $response = $this->client->request('POST', $this->getEndpoint(), [
                'headers' => [
                    'Account' => $this->account,
                    'ApiKey' => $this->apiKey,
                    'Token' => $this->token,
                ],
                RequestOptions::JSON => $params,
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $e) {
            throw CouldNotSendNotification::serviceRespondedWithAnError($e);
        } catch (GuzzleException $e) {
            throw CouldNotSendNotification::couldNotCommunicateWithHablame($e);
        }
    }
}