<?php


namespace bitrix\rest\client;


use bitrix\exception\BitrixClientException;

/**
 * Attempt to make wrapped connection read-only
 *
 * @package bitrix\rest\client
 */
class ReadOnlyDecorator implements BitrixClient
{
    /**
     * @var BitrixClient
     */
    public $client;

    const RESTRICTED_REGEX = '~\b(add|(re)?set|(((un)?bind|modify|update|delete|force).*?))\b~';

    function __construct(BitrixClient $client)
    {
        $this->client = $client;
    }

    public function info(): string
    {
        return $this->client->info() . " in read-only mode";
    }

    public function call(string $method, array $parameters = [])
    {
        if (preg_match(self::RESTRICTED_REGEX, $method)) {
            throw new BitrixClientException("Read-only connection prohibits method $method");
        }
        return $this->client->call($method, $parameters);
    }
}