<?php

namespace bitrix\rest\client;

use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\TransportException;

/**
 * Main calling interface for Bitrix24 REST API
 *
 * @package bitrix\rest\client
 */
interface Bitrix24
{
    /**
     * General information about this connection
     *
     * @return string
     */
    public function info(): string;

    /**
     * Вызов метода api с любыми параметрами
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return array | [ 'result' => array, 'total' => int, 'next' => int|null ] - массив с сущностью или список сущностей с указанием на следующую
     *
     * @throws TransportException
     * @throws BitrixClientException
     * @throws BitrixServerException
     */
    public function call(string $method, array $parameters = []);
}