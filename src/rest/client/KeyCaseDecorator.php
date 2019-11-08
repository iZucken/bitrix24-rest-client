<?php

namespace bitrix\rest\client;

use bitrix\Utility;

/**
 * Automatically convert all keys to upper case
 * Actually dangerous to use since SOMETIMES there are keys that should not be converted
 *
 * @package bitrix\rest\client
 */
class KeyCaseDecorator implements Bitrix24
{
    /**
     * @var Bitrix24
     */
    public $bitrix;

    function __construct(Bitrix24 $bitrix)
    {
        $this->bitrix = $bitrix;
    }

    public function info(): string
    {
        return $this->bitrix->info() . " auto-cased";
    }

    public function call(string $method, array $parameters = [])
    {
        return $this->bitrix->call($method, Utility::recursiveUppercaseKey($parameters));
    }
}