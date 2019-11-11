<?php

namespace bitrix\exception;

/**
 * Thrown when exceptional condition is detected on the server side of the API,
 * in cases where such condition was not described in the response
 *
 * @package bitrix\exception
 */
class UndefinedBitrixServerException extends BitrixServerException
{
}