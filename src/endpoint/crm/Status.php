<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\SystemCrud;
use bitrix\exception\BitrixException;
use bitrix\exception\TransportException;

/**
 * Wraps related CRM CRUD methods
 *
 * @package endpoint
 */
class Status extends SystemCrud
{
    function getScopeName(): string
    {
        return "crm";
    }

    function getScopePath(): string
    {
        return 'crm.status';
    }

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['status'];
    }
}