<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\CommonCrud;
use bitrix\exception\BitrixException;
use bitrix\exception\TransportException;

/**
 * Wrapper for Lead-related CRM CRUD methods
 *
 * // TODO: check how status-based 'require' interacts with api
 *
 * @package endpoint
 */
class Lead extends CommonCrud
{
    function getScopeName(): string
    {
        return "crm";
    }

    function getScopePath(): string
    {
        return 'crm.lead';
    }

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['lead'];
    }
}