<?php

namespace bitrix\rest\client;

/**
 * Интерфейс подключения к REST API Bitrix24
 *
 * @package bitrix\rest\client
 */
interface Bitrix24
{
    /**
     * Общая информация об этом соединении
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
     */
    public function call(string $method, array $parameters = []);
}