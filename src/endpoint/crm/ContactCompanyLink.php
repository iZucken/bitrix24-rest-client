<?php


namespace bitrix\endpoint\crm;


use bitrix\exception\BitrixException;
use bitrix\exception\TransportException;

/**
 * Link companies to contacts
 *
 * @package endpoint
 */
class ContactCompanyLink extends CrmLinkCrud
{
    function getScopeName(): string
    {
        return "crm";
    }

    function getScopePath(): string
    {
        return 'crm.contact.company';
    }

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['contact'];
    }

    function getForeignIdType(): string
    {
        return 'COMPANY_ID';
    }
}