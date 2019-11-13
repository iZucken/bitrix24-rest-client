<?php

namespace bitrix\rest\client;

use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\TransportException;
use bitrix\exception\UndefinedBitrixServerException;

/**
 * Main calling interface for Bitrix24 REST API
 *
 * @package bitrix\rest\client
 */
interface BitrixClient
{
    /**
     * General information about this connection
     *
     * @return string
     */
    public function info(): string;

    /**
     * Call an api method with arbitrary parameters
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return null | int | bool | string | array | [ 'result' => array, 'total' => int, ['next' => int|null] ]
     *
     * @throws TransportException
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws UndefinedBitrixServerException
     */
    public function call(string $method, array $parameters = []);
}