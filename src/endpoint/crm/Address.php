<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\GenericListFilter;
use bitrix\endpoint\Schema;
use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\InputValidationException;
use bitrix\exception\NotFoundException;
use bitrix\exception\TransportException;

/**
 * Wraps related CRM CRUD methods
 *
 * @package endpoint
 */
class Address
{
    /**
     * @var Schema
     */
    protected $schema;

    function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    function getScopeName(): string
    {
        return "crm";
    }

    function getScopePath(): string
    {
        return 'crm.address';
    }

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['address'];
    }

    /**
     * @param array $fields
     * @return int
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    public function add(array $fields): int
    {
        $this->schema->assertValidFields($this->getScopeSettings()['fields'], $fields, false);
        return $this->schema->client->call($this->getScopePath() . '.add', ['FIELDS' => $fields]);
    }

    /**
     * NOTE: UNSET FIELDS WILL BE SAVED AS EMPTY!
     *
     * @param array $fields
     * @return bool
     * @throws BitrixClientException
     * @throws BitrixException
     * @throws BitrixServerException
     * @throws InputValidationException
     * @throws NotFoundException
     * @throws TransportException
     */
    public function update(array $fields): bool
    {
        $this->schema->assertValidFields($this->getScopeSettings()['fields'], $fields, true);
        try {
            return $this->schema->client->call($this->getScopePath() . '.update', ["FIELDS" => $fields]);
        } catch (BitrixServerException $exception) {
            throw $this->convertNotFoundException($exception);
        }
    }

    /**
     * @param int $type
     * @param int $entity
     * @param int $entityType
     * @return bool
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws NotFoundException
     * @throws TransportException
     */
    public function delete(int $type, int $entity, int $entityType): bool
    {
        try {
            return $this->schema->client->call($this->getScopePath() . '.delete', ["TYPE_ID" => $type,"ENTITY_ID" => $entity,"ENTITY_TYPE_ID" => $entityType]);
        } catch (BitrixServerException $exception) {
            throw $this->convertNotFoundException($exception);
        }
    }

    /**
     * @param BitrixServerException $exception
     * @return BitrixServerException|NotFoundException
     */
    function convertNotFoundException(BitrixServerException $exception)
    {
        if (preg_match("~(не найде|not found)~i", $exception)) {
            return new NotFoundException("Entity not found");
        }
        return $exception;
    }

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