<?php

namespace bitrix\exception;

use Exception;

/**
 * Thrown on unexpected API communication problems.
 * Catching this means there are severe problems with connectivity or uptime.
 *
 * @package bitrix\exception
 */
class TransportException extends Exception
{
}