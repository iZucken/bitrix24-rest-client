<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\CrmCrud;
use bitrix\exception\BitrixException;
use bitrix\exception\TransportException;

/**
 * Wraps related CRM CRUD methods
 *
 * @package endpoint
 */
class Requisite extends CrmCrud
{
    function getScopeName(): string
    {
        return "crm";
    }

    function getScopePath(): string
    {
        return 'crm.requisite';
    }

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['requisite'];
    }
}