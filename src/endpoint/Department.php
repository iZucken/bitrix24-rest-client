<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixException;
use bitrix\exception\TransportException;

/**
 * Wraps related General CRUD methods
 *
 * @package endpoint
 */
class Department extends UserDepartmentLegacyCrud
{
    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['department'];
    }

    function getScopeName(): string
    {
        return "department";
    }

    function getScopePath(): string
    {
        return "department";
    }
}