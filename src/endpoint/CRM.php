<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;
use bitrix\exception\ClientInputValidationException;
use bitrix\rest\client\Bitrix24;
use bitrix\storage\Storage;

class CRM
{
    /**
     * @var Bitrix24
     */
    public $bitrix;
    /**
     * @var Storage
     */
    public $storage;

    function __construct(Bitrix24 $bitrix24, Storage $storage)
    {
        $this->bitrix = $bitrix24;
        $this->storage = $storage;
    }

    public $schema = null;

    function pullSchema()
    {
        $statusEntityTypes = $this->bitrix->call('crm.status.entity.types');
        $statusEntityTypes = array_combine(array_column($statusEntityTypes, "ID"), $statusEntityTypes);
        $statusList = $this->bitrix->call('crm.status.list')['result'];
        foreach ($statusList as $status) {
            $statusEntityTypes[$status['ENTITY_ID']]['items'] [] = $status;
        }
        return [
            'crm' => [
                'lead'       => [
                    'fields' => $this->bitrix->call('crm.lead.fields'),
                ],
                'enum'       => [
                    'fields' => $this->bitrix->call('crm.enum.fields'),
                ],
                'multifield' => [
                    'fields' => $this->bitrix->call('crm.multifield.fields'),
                ],
                'currency'   => [
                    'list' => $this->bitrix->call('crm.currency.list')['result'],
                ],
                'status'     => [
                    'list'   => $statusList,
                    'entity' => [
                        'map' => $statusEntityTypes,
                    ],
                ],
            ],
        ];
    }

    function purgeSchema()
    {
        $this->schema = null;
        $this->storage->set("Schema", null);
    }

    function getSchema()
    {
        if (empty($this->schema)) {
            $this->schema = $this->storage->get("Schema");
        }
        if (empty($this->schema)) {
            $schema = $this->pullSchema();
            $this->storage->set("Schema", $schema);
            $this->schema = $schema;
        }
        return $this->schema;
    }

    function assertValidMultifieldValues($values, bool $needsMutation)
    {
        $schema = $this->getSchema()['crm']['multifield']['fields'];
        foreach ($values as $value) {
            foreach ($value as $field => $data) {
                if ($field === "ID") {
                    if (!$needsMutation) {
                        throw new ClientInputValidationException("ID in multi-fields can only be specified on update");
                    }
                    continue;
                }
                $this->assertValidField($schema, $field, $data, $needsMutation);
            }
        }
        return true;
    }

    function assertValidType($schema, $value, bool $needsMutation)
    {
        switch ($schema['type']) {
            case 'int':
            case 'integer':
            case 'crm_company': // TODO: company id check?
            case 'crm_contact': // TODO: contact id check?
            case 'user': // TODO: user id check?
                return is_int($value); // TODO: resolve dangerous assumption
            case 'string':
                return is_string($value);
            case 'char':
                return $value === 'Y' || $value === 'N'; // TODO: resolve dangerous assumption
            case 'date':
                return (bool)strtotime($value); // TODO: determine all valid bitrix date formats, some of them are Y-m-d and d.m.Y
            case 'double':
            case 'float':
                return is_float($value);
            case 'enumeration':
                $possibleValues = array_values(array_column($schema['items'], "ID"));
                return in_array($value, $possibleValues);
            case 'crm_multifield':
                return $this->assertValidMultifieldValues($value, $needsMutation);
            case 'crm_status':
                return in_array($value,
                    array_column($this->schema['crm']['status']['entity']['map'][$schema['statusType']]['items'],
                        "STATUS_ID"));
            case 'crm_currency':
                return in_array($value, array_column($this->schema['crm']['currency']['list'], 'CURRENCY'));
        }
        throw new BitrixClientException("Encountered unknown type {$schema['type']} in a schema");
    }

    function assertValidField(array $schema, string $field, $value, bool $needsMutation)
    {
        if (empty($schema[$field])) {
            throw new ClientInputValidationException("Field '$field' does not exist");
        }
        $fieldSchema = $schema[$field];
        if ($fieldSchema['isReadOnly']) {
            throw new ClientInputValidationException("Field '$field' is read-only");
        }
        if ($fieldSchema['isImmutable'] && $needsMutation) {
            throw new ClientInputValidationException("Field '$field' cannot be changed after being saved");
        }
        if ($fieldSchema['isMultiple'] && !is_array($value)) {
            throw new ClientInputValidationException("Field '$field' value should be array, " . gettype($value) . " given");
        }
        if (!$this->assertValidType($fieldSchema, $value, $needsMutation)) {
            throw new ClientInputValidationException("Field '$field' value does not conform to '{$fieldSchema['type']}' type");
        }
    }

    function assertValidFields(array $schema, array $fields, bool $needsMutation)
    {
        foreach ($fields as $field => $value) {
            $this->assertValidField($schema, $field, $value, $needsMutation);
        }
    }

    function assertValidFilter(array $schema, array $filter)
    {

    }

    function parseListFilter(string $field)
    {
        $matches = [];
        $matched = preg_match("~(|=|!|%|<|>|<=|>=)(\w+)~", $field, $matches);
        if (!$matched) {
            throw new BitrixClientException("Invalid filter $field");
        }
        return ['type' => $matches[1], 'field' => $matches[2]];
    }
}