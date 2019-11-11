<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\CommonCrud;

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

    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['lead'];
    }
}