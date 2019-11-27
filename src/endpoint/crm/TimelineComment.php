<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\CrmCrud;
use bitrix\endpoint\GenericListFilter;
use bitrix\exception\BitrixException;
use bitrix\exception\InputValidationException;
use bitrix\exception\NotFoundException;
use bitrix\exception\TransportException;

/**
 * Wraps related CRM CRUD methods
 *
 * @package endpoint
 */
class TimelineComment extends CrmCrud
{
    function getScopeName(): string
    {
        return "crm";
    }

    function getScopePath(): string
    {
        return 'crm.timeline.comment';
    }

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['timeline']['comment'];
    }

    /**
     * Additional required parameters:
     * ENTITY_ID
     * ENTITY_TYPE
     *
     * @param array $fields
     * @return int
     * @throws BitrixException
     * @throws InputValidationException
     * @throws TransportException
     */
    function add(array $fields): int
    {
        if (empty($fields['ENTITY_ID'])) {
            throw new InputValidationException("Required parameter 'ENTITY_ID' is missing");
        }
        if (empty($fields['ENTITY_TYPE'])) {
            throw new InputValidationException("Required parameter 'ENTITY_TYPE' is missing");
        }
        $types = $this->getScopeSettings()['entityTypes'];
        if (!in_array($fields['ENTITY_TYPE'], $types)) {
            throw new InputValidationException("Parameter 'ENTITY_TYPE' must be one of " . join(", ", $types));
        }
        return parent::add($fields);
    }

    /**
     * Additional required parameters:
     * ENTITY_ID
     * ENTITY_TYPE
     *
     * @param GenericListFilter $filter
     * @return array
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     * @throws NotFoundException - when out of list bounds
     */
    function list(GenericListFilter $filter): array
    {
        if (empty($filter->getFilter()['ENTITY_ID'])) {
            throw new InputValidationException("Required parameter 'ENTITY_ID' is missing");
        }
        if (empty($filter->getFilter()['ENTITY_TYPE'])) {
            throw new InputValidationException("Required parameter 'ENTITY_TYPE' is missing");
        }
        $types = $this->getScopeSettings()['entityTypes'];
        if (!in_array($filter->getFilter()['ENTITY_TYPE'], $types)) {
            throw new InputValidationException("Parameter 'ENTITY_TYPE' must be one of " . join(", ", $types));
        }
        return parent::list($filter);
    }
}