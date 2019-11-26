<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixException;
use bitrix\exception\TransportException;

/**
 * Wraps related General CRUD methods
 *
 * @package bitrix\endpoint
 */
class User extends UserDepartmentLegacyCrud
{
    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['user'];
    }

    function getScopeName(): string
    {
        return "user";
    }

    function getScopePath(): string
    {
        return "user";
    }
}