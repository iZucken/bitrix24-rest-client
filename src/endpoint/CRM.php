<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixException;
use bitrix\exception\InputValidationException;
use bitrix\exception\TransportException;
use bitrix\rest\client\Bitrix24;
use bitrix\storage\Storage;

/**
 * Provides schema aggregation and assertion for CRM endpoints
 *
 * TODO: fields size quality validation
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

    public $schema;

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function pullSchema(): array
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

    function purgeSchema(): void
    {
        $this->schema = null;
        $this->storage->set("Schema", null);
    }

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getSchema(): array
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

    /**
     * @param array $values
     * @param bool  $needsMutation
     * @return bool
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    function assertValidMultifieldValues(array $values, bool $needsMutation): bool
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

    /**
     * @param array $schema
     * @param mixed $value
     * @param bool  $needsMutation
     * @return bool
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    function assertValidType(array $schema, $value, bool $needsMutation): bool
    {
        switch ($schema['type']) {
            case 'int':
            case 'integer':
            case 'crm_company':
            case 'crm_contact':
            case 'user':
                return is_int($value);
            case 'string':
                return is_string($value);
            case 'char':
                return $value === 'Y' || $value === 'N'; // TODO: find a way to resolve this dangerous assumption
            case 'date': // TODO: determine all valid bitrix date formats, some of them are 'Y-m-d' and 'd.m.Y'; or choose a single one
            case 'datetime': // TODO: appears to conform with common SQL timestamp format, validate that
                return (bool)strtotime($value);
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

    /**
     * @param array  $schema
     * @param string $field
     * @param mixed  $value
     * @param bool   $needsMutation
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    function assertValidField(array $schema, string $field, $value, bool $needsMutation): void
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

    /**
     * @param array $schema
     * @param array $fields
     * @param bool  $needsMutation
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    function assertValidFields(array $schema, array $fields, bool $needsMutation): void
    {
        foreach ($fields as $field => $value) {
            $this->assertValidField($schema, $field, $value, $needsMutation);
        }
    }

    /**
     * @param array $schema
     * @param array $filter
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    function assertValidFilter(array $schema, array $filter): void
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
                $field = $this->parseListFilter($filterField)[1];
                if (empty($schema[$field])) {
                    throw new InputValidationException("In filter 'FILTER' field '$field' does not exist");
                }
                $fieldSchema = $schema[$field];
                if ($fieldSchema['type'] === 'crm_multifield' && !is_string($value)) {
                    throw new InputValidationException("In filter 'FILTER' multi-fields like '$field' can only be filtered by a string");
                } else {
                    if (is_array($value)) {
                        foreach ($value as $datum) {
                            if (!$this->assertValidType($fieldSchema, $datum, false)) {
                                throw new InputValidationException("When using array of values in 'FILTER' field '$field' they all must conform to '{$fieldSchema['type']}' type");
                            }
                        }
                    } else {
                        if (!$this->assertValidType($fieldSchema, $value, false)) {
                            throw new InputValidationException("In filter 'FILTER' field '$field' value does not conform to '{$fieldSchema['type']}' type");
                        }
                    }
                }
            }
        }
        if (isset($filter['START']) && (!is_int($filter['START']) || ($filter['START'] % 50 != 0))) {
            throw new InputValidationException("Filter 'START' parameter must be an integer multiple of 50");
        }
    }

    /**
     * @param string $field
     * @return array(string filterType, string filterField)
     * @throws InputValidationException
     */
    function parseListFilter(string $field): array
    {
        $matches = [];
        $matched = preg_match("~(|=|!|%|<|>|<=|>=)(\w+)~", $field, $matches);
        if (!$matched) {
            throw new InputValidationException("Invalid filter '$field'");
        }
        return [$matches[1], $matches[2]];
    }
}