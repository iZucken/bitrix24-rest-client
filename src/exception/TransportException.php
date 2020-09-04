<?php

namespace bitrix\exception;

use Exception;
use Throwable;

/**
 * Thrown on unexpected API communication problems.
 * Catching this means there are severe problems with connectivity or uptime.
 *
 * @package bitrix\exception
 */
class TransportException extends Exception
{
    protected $content;

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null, string $content = null)
    {
        $this->content = $content;
        parent::__construct($message, $code, $previous);
    }

    public function getContent()
    {
        return $this->content;
    }
}