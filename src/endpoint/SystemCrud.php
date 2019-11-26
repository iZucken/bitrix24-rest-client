<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixException;
use bitrix\exception\InputValidationException;
use bitrix\exception\TransportException;

/**
 * Wrapper for system-related CRUD methods
 *
 * @package endpoint
 */
abstract class SystemCrud extends CommonCrud
{

    /**
     * @param SystemListFilter $filter
     * @return array
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    public function list(SystemListFilter $filter): array
    {
        $this->schema->assertValidSystemFilter($this->getScopeSettings()['fields'], $filter);
        return $this->schema->client->call($this->getScopePath() . '.list', $filter->toFullMap());
    }
}