<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\CRM;
use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\InputValidationException;
use bitrix\exception\TransportException;

/**
 * Wrapper for CRUD methods of the Lead-related CRM methods
 *
 * // TODO: check how status-based 'require' interacts with api
 *
 * @package endpoint
 */
class Lead
{
    /**
     * @var CRM
     */
    private $crm;

    function __construct(CRM $crm)
    {
        $this->crm = $crm;
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
        $this->crm->assertValidFields($this->crm->getSchema()['crm']['lead']['fields'], $fields, false);
        return $this->crm->bitrix->call('crm.lead.add', ['FIELDS' => $fields]);
    }

    /**
     * @param int $id
     * @return array
     * @throws TransportException
     * @throws BitrixClientException
     * @throws BitrixServerException
     */
    public function get(int $id): array
    {
        return $this->crm->bitrix->call('crm.lead.get', ['ID' => $id]);
    }

    /**
     * @param int   $id
     * @param array $fields
     * @return bool
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    public function update(int $id, array $fields): bool
    {
        $this->crm->assertValidFields($this->crm->getSchema()['crm']['lead']['fields'], $fields, true);
        return $this->crm->bitrix->call('crm.lead.update', ["ID" => $id, "FIELDS" => $fields]);
    }

    /**
     * @param int $id
     * @return bool
     * @throws BitrixException
     * @throws TransportException
     */
    public function delete(int $id): bool
    {
        return $this->crm->bitrix->call('crm.lead.delete', ["ID" => $id]);
    }

    /**
     * @param array $filter
     * @return array
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    function list(array $filter): array
    {
        $this->crm->assertValidFilter($this->crm->getSchema()['crm']['lead']['fields'], $filter);
        return $this->crm->bitrix->call('crm.lead.list', $filter);
    }
}