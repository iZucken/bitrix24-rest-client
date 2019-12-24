<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixException;
use bitrix\exception\InputValidationException;
use bitrix\exception\NotFoundException;
use bitrix\exception\TransportException;
use bitrix\rest\client\BitrixClient;
use bitrix\storage\Storage;

/**
 * Provides schema aggregation and assertion for endpoints
 *
 * TODO: convert schematic fields to their corresponding types to ease end-user surprise load
 *
 * @package bitrix\endpoint
 */
class Schema
{
    /**
     * @var BitrixClient
     */
    public $client;
    /**
     * @var Storage
     */
    public $storage;

    function __construct(BitrixClient $bitrix, Storage $storage)
    {
        $this->client = $bitrix;
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
        $scope = $this->client->call('scope');
        $scope = array_combine($scope, $scope);
        $schema = [
            'scope' => $scope,
        ];
        if (isset($schema['scope']['crm'])) {
            $statusEntityTypes = $this->client->call('crm.status.entity.types');
            $statusEntityTypes = array_combine(array_column($statusEntityTypes, "ID"), $statusEntityTypes);
            $statusList = $this->client->call('crm.status.list')['result'];
            foreach ($statusList as $status) {
                $statusEntityTypes[$status['ENTITY_ID']]['items'] [] = $status;
            }
            $productFields = $this->client->call('crm.product.fields');
            $productFieldsMap = [];
            foreach ($productFields as $key => $productField) {
                if (preg_match("~^PROPERTY_\d+$~", $key)) {
                    $productFieldsMap[$productField['title']] = $key;
                } else {
                    $productFieldsMap[$key] = $key;
                }
            }
            $schema['crm'] = [
                'lead'       => [
                    'fields' => $this->client->call('crm.lead.fields'),
                ],
                'contact'    => [
                    'fields' => $this->client->call('crm.contact.fields'),
                ],
                'deal'       => [
                    'fields' => $this->client->call('crm.deal.fields'),
                ],
                'company'    => [
                    'fields' => $this->client->call('crm.company.fields'),
                ],
                'address'    => [
                    'fields' => $this->client->call('crm.address.fields'),
                ],
                'requisite'  => [
                    'fields'     => $this->client->call('crm.requisite.fields'),
                    'bankdetail' => [
                        'fields' => $this->client->call('crm.requisite.bankdetail.fields'),
                    ],
                ],
                'enum'       => [
                    'fields' => $this->client->call('crm.enum.fields'),
                ],
                'multifield' => [
                    'fields' => $this->client->call('crm.multifield.fields'),
                ],
                'product' => [
                    'fields' => $productFields,
                    'fieldsMap' => $productFieldsMap,
                ],
                'currency'   => [
                    'list' => $this->client->call('crm.currency.list')['result'],
                ],
                'timeline'   => [
                    'comment' => [
                        'entityTypes' => ['lead', 'deal', 'contact', 'company', 'order'],
                        'fields'      => $this->client->call('crm.timeline.comment.fields'),
                    ],
                ],
                'status'     => [
                    'fields' => $this->client->call('crm.status.fields'),
                    'list'   => $statusList,
                    'entity' => [
                        'map' => $statusEntityTypes,
                    ],
                ],
            ];
        }
        // TODO: determine mechanisms for core entities mutation control?
        if (isset($schema['scope']['user'])) {
            $fields = [];
            foreach ([
                         'GENDER',
                         'PROFESSION',
                         'WWW',
                         'BIRTHDAY',
                         'PHOTO',
                         'ICQ',
                         'PHONE',
                         'FAX',
                         'MOBILE',
                         'PAGER',
                         'STREET',
                         'CITY',
                         'STATE',
                         'ZIP',
                         'COUNTRY',
                     ] as $field) {
                $fields['PERSONAL_' . $field] = [
                    'type'        => 'string',
                    'isReadOnly'  => true,
                    'isImmutable' => true,
                    'isMultiple'  => false,
                ];
            }
            foreach ([ // user fields pre-made by system
                         'INTERESTS',
                         'SKILLS',
                         'WEB_SITES',
                         'XING',
                         'LINKEDIN',
                         'FACEBOOK',
                         'TWITTER',
                         'SKYPE',
                         'DISTRICT',
                         'PHONE_INNER',
                     ] as $field) {
                $fields['UF_' . $field] = [
                    'type'        => 'string',
                    'isReadOnly'  => true,
                    'isImmutable' => true,
                    'isMultiple'  => false,
                ];
            }
            $schema['user'] = [
                'fields' => array_merge($fields, [
                    'ID'            => [
                        'type'        => 'int',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    // special fields
                    'NAME_SEARCH'   => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'IS_ONLINE'     => [
                        'type'        => 'char',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    /*
                    USER_TYPE - тип пользователя. Может принимать следующие значения:
                    employee - сотрудник,
                    extranet - пользователь экстранета,
                    email - почтовый пользователь
                     */
                    'USER_TYPE'     => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'ACTIVE'        => [
                        'type'        => 'char',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'NAME'          => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'LAST_NAME'     => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'SECOND_NAME'   => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'EMAIL'         => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'WORK_COMPANY'  => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'WORK_POSITION' => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'WORK_PHONE'    => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'UF_DEPARTMENT' => [
                        'type'        => 'int',
                        'isReadOnly'  => false,
                        'isImmutable' => false,
                        'isMultiple'  => true,
                    ],
                ]),
            ];
        }
        if (isset($schema['scope']['department'])) {
            $schema['department'] = [
                'fields' => [
                    'ID'      => [
                        'type'        => 'int',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'NAME'    => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'SORT'    => [
                        'type'        => 'int',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'PARENT'  => [
                        'type'        => 'int',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'UF_HEAD' => [
                        'type'        => 'int',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                ],
            ];
        }
        return $schema;
    }

    /**
     * @param string $scope
     * @throws BitrixClientException
     * @throws BitrixException
     * @throws TransportException
     */
    function assertInScope(string $scope)
    {
        $scopes = $this->getSchema()['scope'];
        if (!isset($scopes[$scope])) {
            throw new BitrixClientException("Current schema has no access to scope '$scope', available: " .
                join(", ", $scopes));
        }
    }

    function purgeSchema()
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
     * @param array $value
     * @param bool  $needsMutation
     * @return bool
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    function assertValidMultifieldValue(array $value, bool $needsMutation): bool
    {
        $schema = $this->getSchema()['crm']['multifield']['fields'];
        foreach ($value as $field => $data) {
            if ($field === "ID") {
                if (!$needsMutation) {
                    throw new InputValidationException("ID in multi-fields can only be specified on update");
                }
                continue;
            }
            $this->assertValidField($schema, $field, $data, $needsMutation);
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
            case 'bool':
                return $value === true || $value === false;
            case 'char':
                return $value === 'Y' || $value === 'N'; // TODO: find a GOOD way to resolve this dangerous assumption, there are other types of char fields
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
                return $this->assertValidMultifieldValue($value, $needsMutation);
            case 'crm_status':
                return in_array($value,
                    array_column($this->schema['crm']['status']['entity']['map'][$schema['statusType']]['items'],
                        "STATUS_ID"));
            case 'crm_currency':
                return in_array($value, array_column($this->schema['crm']['currency']['list'], 'CURRENCY'));
            case 'unchecked':
            case 'product_property':
            case 'product_file':
                return true;
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
        if ($fieldSchema['isMultiple']) {
            if (!is_array($value)) {
                throw new InputValidationException("Field '$field' value should be array, " . gettype($value) . " given");
            } else {
                foreach ($value as $item) {
                    if (!$this->assertValidType($fieldSchema, $item, $needsMutation)) {
                        throw new InputValidationException("Field '$field' value does not conform to '{$fieldSchema['type']}' type");
                    }
                }
            }
        } else {
            if (!$this->assertValidType($fieldSchema, $value, $needsMutation)) {
                throw new InputValidationException("Field '$field' value does not conform to '{$fieldSchema['type']}' type");
            }
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
    function assertValidFields(array $schema, array $fields, bool $needsMutation)
    {
        foreach ($fields as $field => $value) {
            $this->assertValidField($schema, $field, $value, $needsMutation);
        }
    }

    /**
     * @param array $schema
     * @param array $order
     * @throws InputValidationException
     */
    function assertValidFilterOrder(array $schema, array $order)
    {
        foreach ($order as $field => $value) {
            if (!($value === 'ASC' || $value === 'DESC')) {
                throw new InputValidationException("Filter order values must be either 'ASC' or 'DESC'");
            }
            if (empty($schema[$field])) {
                throw new InputValidationException("In filter order field '$field' does not exist");
            }
            if ($schema[$field]['isMultiple'] || $schema[$field]['type'] === 'crm_multifield') {
                throw new InputValidationException("In filter order multi-fields like '$field' are not allowed");
            }
        }
    }

    /**
     * @param array  $schema
     * @param string $field
     * @param mixed  $value
     * @throws BitrixException
     * @throws InputValidationException
     * @throws TransportException
     */
    function assertValidFilterFieldValue(array $schema, string $field, $value)
    {
        if (empty($schema[$field])) {
            throw new InputValidationException("In filter field '$field' does not exist");
        }
        $fieldSchema = $schema[$field];
        if ($fieldSchema['type'] === 'crm_multifield') {
            if (!is_string($value)) {
                throw new InputValidationException("In filter multi-fields like '$field' can only be filtered by a string");
            }
        } else {
            if (is_array($value)) {
                foreach ($value as $datum) {
                    if (!$this->assertValidType($fieldSchema, $datum, false)) {
                        throw new InputValidationException("When using array of values in a filter field '$field' they all must conform to '{$fieldSchema['type']}' type");
                    }
                }
            } else {
                if (!$this->assertValidType($fieldSchema, $value, false)) {
                    throw new InputValidationException("In filter field '$field' value does not conform to '{$fieldSchema['type']}' type");
                }
            }
        }
    }

    /**
     * @param array             $schema
     * @param GenericListFilter $filter
     * @param bool              $withConditionals
     * @throws BitrixException
     * @throws InputValidationException
     * @throws TransportException
     */
    function assertValidFilter(array $schema, GenericListFilter $filter, bool $withConditionals = true)
    {
        $this->assertValidFilterOrder($schema, $filter->getOrder());
        foreach ($filter->getSelect() as $valueField) {
            if (!(
                isset($schema[$valueField]) ||
                $valueField === '*' || // special mask for all non-multiple standard fields
                $valueField === 'UF_*' // special mask for all non-multiple user-fields
            )) {
                throw new InputValidationException("In filter select field '$valueField' does not exist");
            }
        }
        foreach ($filter->getFilter() as $filterField => $value) {
            if ($withConditionals) {
                $field = $this->parseListFilter($filterField)[1];
            } else {
                $field = $filterField;
            }
            $this->assertValidFilterFieldValue($schema, $field, $value);
        }
        self::assertValidFilterStart($filter->getStart());
    }

    /**
     * @param array            $schema
     * @param SystemListFilter $filter
     * @throws BitrixException
     * @throws InputValidationException
     * @throws TransportException
     */
    function assertValidSystemFilter(array $schema, SystemListFilter $filter)
    {
        $this->assertValidFilterOrder($schema, $filter->getOrder());
        foreach ($filter->getFilter() as $field => $value) {
            $this->assertValidFilterFieldValue($schema, $field, $value);
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
        $matched = preg_match("~^(|=|!|%|<|>|<=|>=)(\w+)$~", $field, $matches);
        if (!$matched) {
            throw new InputValidationException("Invalid filter '$field'");
        }
        return [$matches[1], $matches[2]];
    }

    /**
     * @param int $start
     * @throws InputValidationException
     */
    static function assertValidFilterStart(int $start)
    {
        if ($start < 0 || $start % 50 != 0) {
            throw new InputValidationException("Filter start must be a positive multiple of 50");
        }
    }

    /**
     * @param GenericListFilter $filter
     * @param array             $listResponse
     * @throws NotFoundException
     */
    function assertListResponseInbound(GenericListFilter $filter, array $listResponse)
    {
        if ($filter->getStart() > $listResponse['total']) {
            throw new NotFoundException("Filter reached out of list bounds");
        }
        if (isset($listResponse['next'])) {
            if ($listResponse['next'] != ($filter->getStart() + 50)) {
                throw new NotFoundException("Filter reached out of list bounds");
            }
        }
    }
}