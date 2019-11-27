<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\NotFoundException;
use bitrix\exception\InputValidationException;
use bitrix\exception\TransportException;

/**
 * Wrapper for common CRUD methods
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

    /**
     * @return array
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws TransportException
     */
    abstract function getScopeSettings(): array;

    abstract function getScopeName(): string;

    abstract function getScopePath(): string;

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
     * @param int $id
     * @return array | null
     * @throws NotFoundException
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws TransportException
     */
    public function get(int $id)
    {
        try {
            return $this->schema->client->call($this->getScopePath() . '.get', ['ID' => $id]);
        } catch (BitrixServerException $exception) {
            throw $this->convertNotFoundException($exception);
        }
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
            throw $this->convertNotFoundException($exception);
        }
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
}