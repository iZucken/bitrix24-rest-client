<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\SystemCrud;

/**
 * Wrapper for CRM statuses CRUD methods
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
     * @throws \bitrix\exception\BitrixException
     * @throws \bitrix\exception\TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['status'];
    }
}