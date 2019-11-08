<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;
use bitrix\exception\InputValidationException;
use bitrix\rest\client\Bitrix24;
use bitrix\storage\Storage;

/**
 * Provides schema aggregation and assertion for CRM endpoints
 *
 * @package bitrix\endpoint
 */
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
                        throw new InputValidationException("ID in multi-fields can only be specified on update");
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
        throw new BitrixClientException("Encountered unknown type '{$schema['type']}' in a schema");
    }

    function assertValidField(array $schema, string $field, $value, bool $needsMutation)
    {
        if (empty($schema[$field])) {
            throw new InputValidationException("Field '$field' does not exist");
        }
        $fieldSchema = $schema[$field];
        if ($fieldSchema['isReadOnly']) {
            throw new InputValidationException("Field '$field' is read-only");
        }
        if ($fieldSchema['isImmutable'] && $needsMutation) {
            throw new InputValidationException("Field '$field' cannot be changed after being saved");
        }
        if ($fieldSchema['isMultiple'] && !is_array($value)) {
            throw new InputValidationException("Field '$field' value should be array, " . gettype($value) . " given");
        }
        if (!$this->assertValidType($fieldSchema, $value, $needsMutation)) {
            throw new InputValidationException("Field '$field' value does not conform to '{$fieldSchema['type']}' type");
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
        if (isset($filter['ORDER'])) {
            if (!is_array($filter['ORDER'])) {
                throw new InputValidationException("Filter 'ORDER' parameter must be an array");
            }
            foreach ($filter['ORDER'] as $field => $value) {
                if (!($value === 'ASC' || $value === 'DESC')) {
                    throw new InputValidationException("Filter 'ORDER' field values must be either 'ASC' or 'DESC'");
                }
                if (empty($schema[$field])) {
                    throw new InputValidationException("In filter 'ORDER' field '$field' does not exist");
                }
                if ($schema[$field]['type'] === 'crm_multifield') {
                    throw new InputValidationException("In filter 'ORDER' multi-fields like '$field' are not allowed");
                }
            }
        }
        if (isset($filter['SELECT'])) {
            if (!is_array($filter['SELECT'])) {
                throw new InputValidationException("Filter 'SELECT' parameter must be an array");
            }
            foreach ($filter['SELECT'] as $valueField) {
                if (!(
                    isset($schema[$valueField]) ||
                    $valueField === '*' || // special mask for all non-multiple standard fields
                    $valueField === 'UF_*' // special mask for all non-multiple user-fields
                )) {
                    throw new InputValidationException("In filter 'SELECT' field '$valueField' does not exist");
                }
            }
        }
        if (isset($filter['FILTER'])) {
            if (!is_array($filter['FILTER'])) {
                throw new InputValidationException("Filter 'FILTER' parameter must be an array");
            }
            foreach ($filter['FILTER'] as $filterField => $value) {
                [$filterType, $field] = $this->parseListFilter($filterField);
                if (empty($schema[$field])) {
                    throw new InputValidationException("In filter 'FILTER' field '$field' does not exist");
                }
                $fieldSchema = $schema[$field];
                if ($fieldSchema['type'] === 'crm_multifield') {
                    // TODO: support for multi-fields
                    throw new InputValidationException("In filter 'FILTER' multi-fields like '$field' are not allowed");
                } else {
                    // TODO: array case validation
                    if (!$this->assertValidType($fieldSchema, $value, false)) {
                        throw new InputValidationException("In filter 'FILTER' field '$field' value does not conform to '{$fieldSchema['type']}' type");
                    }
                }
            }
        }
        if (isset($filter['START']) && (!is_int($filter['START']) || ($filter['START'] % 50 != 0))) {
            throw new InputValidationException("Filter 'START' parameter must be an integer multiple of 50");
        }
    }

    function parseListFilter(string $field)
    {
        $matches = [];
        $matched = preg_match("~(|=|!|%|<|>|<=|>=)(\w+)~", $field, $matches);
        if (!$matched) {
            throw new InputValidationException("Invalid filter '$field'");
        }
        return [$matches[1], $matches[2]];
    }
}