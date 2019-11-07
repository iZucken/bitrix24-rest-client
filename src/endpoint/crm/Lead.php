<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\CRM;

/**
 * Wrapper for some of the lead-related CRM API methods
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

    public function add($fields): int
    {
        $this->crm->assertValidFields($this->crm->getSchema()['crm']['lead']['fields'], $fields, false);
        return $this->crm->bitrix->call('crm.lead.add', ['FIELDS' => $fields]);
    }

    public function get(int $id)
    {
        return $this->crm->bitrix->call('crm.lead.get', ['ID' => $id]);
    }

    public function update(int $id, $fields)
    {
        $this->crm->assertValidFields($this->crm->getSchema()['crm']['lead']['fields'], $fields, true);
        return $this->crm->bitrix->call('crm.lead.update', ["ID" => $id, "FIELDS" => $fields]);
    }

    public function delete(int $id)
    {
        return $this->crm->bitrix->call('crm.lead.delete', ["ID" => $id]);
    }

    function list(array $filter)
    {
        $this->crm->assertValidFilter($this->crm->getSchema()['crm']['lead']['fields'], $filter);
        return $this->crm->bitrix->call('crm.lead.list', $filter);
    }
}