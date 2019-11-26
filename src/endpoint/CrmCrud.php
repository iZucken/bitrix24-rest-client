<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixException;
use bitrix\exception\NotFoundException;
use bitrix\exception\InputValidationException;
use bitrix\exception\TransportException;

/**
 * Wrapper for CRM CRUD methods
 *
 * // TODO: check how status-based 'require' interacts with api
 *
 * @package endpoint
 */
abstract class CrmCrud extends CommonCrud
{
    /**
     * @param GenericListFilter $filter
     * @return array
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     * @throws NotFoundException - when out of list bounds
     */
    public function list(GenericListFilter $filter): array
    {
        $this->schema->assertValidFilter($this->getScopeSettings()['fields'], $filter);
        $list = $this->schema->client->call($this->getScopePath() . '.list', $filter->toFullMap());
        $this->schema->assertListResponseInbound($filter, $list);
        return $list;
    }
}