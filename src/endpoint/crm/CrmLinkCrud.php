<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\Schema;
use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\NotFoundException;
use bitrix\exception\TransportException;

/**
 * Wrapper for common CRUD methods
 *
 * @package endpoint
 */
abstract class CrmLinkCrud
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

    abstract function getForeignIdType(): string;

    abstract function getScopePath(): string;

    /**
     * @param int       $id
     * @param int       $foreignId
     * @param int|null  $sort
     * @param bool|null $is_primary
     * @return bool
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws NotFoundException
     * @throws TransportException
     */
    public function link(int $id, int $foreignId, int $sort = null, bool $is_primary = null): bool
    {
        try {
            return $this->schema->client->call($this->getScopePath() . '.add',
                [
                    "ID"     => $id,
                    "FIELDS" => [
                        $this->getForeignIdType() => $foreignId,
                        'IS_PRIMARY'              => $is_primary ? 'Y' : 'N',
                        'SORT'                    => $sort,
                    ],
                ]);
        } catch (BitrixServerException $exception) {
            throw $this->convertNotFoundException($exception);
        }
    }

    /**
     * @param int $id
     * @param int $foreignId
     * @return bool
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws NotFoundException
     * @throws TransportException
     */
    public function unlink(int $id, int $foreignId): bool
    {
        try {
            return $this->schema->client->call($this->getScopePath() . '.delete',
                ["ID" => $id, "FIELDS" => [$this->getForeignIdType() => $foreignId]]);
        } catch (BitrixServerException $exception) {
            throw $this->convertNotFoundException($exception);
        }
    }

    /**
     * @param int $id
     * @return array
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws NotFoundException
     * @throws TransportException
     */
    public function list(int $id): array
    {
        try {
            return $this->schema->client->call($this->getScopePath() . '.items.get', ["ID" => $id]);
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
    public function clear(int $id): bool
    {
        try {
            return $this->schema->client->call($this->getScopePath() . '.items.delete', ["ID" => $id]);
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