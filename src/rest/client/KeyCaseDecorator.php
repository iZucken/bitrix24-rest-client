<?php

namespace bitrix\rest\client;

use bitrix\exception\BitrixClientException;
use bitrix\Utility;

/**
 * Wrap your bitrix connection to automatically convert all keys to upper case
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

    public function recursiveUppercaseKey($argument)
    {
        if (is_array($argument) && !Utility::isPlainArray($argument)) {
            $upperCased = [];
            foreach ($argument as $key => $value) {
                $casedKey = strtoupper($key);
                if (isset($upperCased[$casedKey])) {
                    throw new BitrixClientException("Map key casing collision for '$key'");
                }
                $upperCased[$casedKey] = $this->recursiveUppercaseKey($value);
            }
            return $upperCased;
        }
        return $argument;
    }

    public function call(string $method, array $parameters = [])
    {
        return $this->bitrix->call($method, $this->recursiveUppercaseKey($parameters));
    }
}