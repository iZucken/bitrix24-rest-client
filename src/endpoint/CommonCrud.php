<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\NotFoundException;
use bitrix\exception\InputValidationException;
use bitrix\exception\TransportException;

/**
 * Wrapper for CRUD methods of the Lead-related CRM methods
 *
 * // TODO: check how status-based 'require' interacts with api
 *
 * @package endpoint
 */
abstract class CommonCrud
{
    /**
     * @var Schema
     */
    protected $schema;

    function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    abstract function getScopeSettings(): array;

    abstract function getScopeName(): string;

    abstract function getScopePath(): string;

    /**
     * @throws BitrixClientException
     */
    public function assertScope(): void
    {
        $this->schema->assertInScope($this->getScopeName());
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
        $this->schema->assertValidFields($this->schema->getSchema()['crm']['lead']['fields'], $fields, false);
        return $this->schema->client->call($this->getScopePath() . '.add', ['FIELDS' => $fields]);
    }

    /**
     * @param int $id
     * @return array
     * @throws NotFoundException
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws TransportException
     */
    public function get(int $id): ?array
    {
        try {
            return $this->schema->client->call($this->getScopePath() . '.get', ['ID' => $id]);
        } catch (BitrixServerException $exception) {
            $this->convertNotFoundException($exception);
        }
        return null;
    }

    /**
     * @param int   $id
     * @param array $fields
     * @return bool
     * @throws InputValidationException
     * @throws NotFoundException
     * @throws BitrixException
     * @throws TransportException
     */
    public function update(int $id, array $fields): bool
    {
        $this->schema->assertValidFields($this->getScopeSettings()['fields'], $fields, true);
        try {
            return $this->schema->client->call($this->getScopePath() . '.update', ["ID" => $id, "FIELDS" => $fields]);
        } catch (BitrixServerException $exception) {
            $this->convertNotFoundException($exception);
        }
        return false;
    }

    /**
     * @param int $id
     * @return bool
     * @throws NotFoundException
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws TransportException
     */
    public function delete(int $id): bool
    {
        try {
            return $this->schema->client->call($this->getScopePath() . '.delete', ["ID" => $id]);
        } catch (BitrixServerException $exception) {
            $this->convertNotFoundException($exception);
        }
        return false;
    }

    /**
     * @param BitrixServerException $exception
     * @throws NotFoundException
     * @throws BitrixServerException
     */
    function convertNotFoundException(BitrixServerException $exception): void
    {
        if (preg_match("~(не найде|not found)~i", $exception)) {
            throw new NotFoundException("Entity not found");
        }
        throw $exception;
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
        $this->schema->listResponseInbound($filter, $list);
        return $list;
    }
}