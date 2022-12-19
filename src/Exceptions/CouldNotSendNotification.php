<?php

namespace Sideso\Hablame\Exceptions;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class CouldNotSendNotification extends \Exception
{
    public static function accountNotProvided()
    {
        return new static("Account not provided.");
    }


    public static function apiKeyNotProvided()
    {
        return new static ("API Key not provided.");
    }

    public static function tokenNotProvided()
    {
        return new static ("Token not provided.");
    }

    /**
     * Thrown when content length is greater than 918 characters.
     *
     * @param $count
     * @return static
     */
    public static function contentLengthLimitExceeded($count): self
    {
        return new static("Notification was not sent. Content length may not be greater than {$count} characters.", 422);
    }

    /**
     * Thrown when we're unable to communicate with Hablame.
     *
     * @param ClientException $exception
     *
     * @return static
     */
    public static function serviceRespondedWithAnError(ClientException $exception): self
    {
        if (! $exception->hasResponse()) {
            return new static('Hablame responded with an error but no response body found');
        }

        return new static("Hablame responded with an error '{$exception->getCode()} : {$exception->getMessage()}'", $exception->getCode(), $exception);
    }

    /**
     * Thrown when we're unable to communicate with Hablame.
     *
     * @param Exception $exception
     *
     * @return static
     */
    public static function couldNotCommunicateWithHablame(GuzzleException $exception): self
    {
        dd($exception->getResponse()->getBody()->getContents());
        return new static("The communication with Hablame failed. Reason: {$exception->getMessage()}", $exception->getCode(), $exception);
    }
}
