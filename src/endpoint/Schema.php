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
 * TODO: fields size quality validation?
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
            $schema['crm'] = [
                'lead'       => [
                    'fields' => $this->client->call('crm.lead.fields'),
                ],
                'enum'       => [
                    'fields' => $this->client->call('crm.enum.fields'),
                ],
                'multifield' => [
                    'fields' => $this->client->call('crm.multifield.fields'),
                ],
                'currency'   => [
                    'list' => $this->client->call('crm.currency.list')['result'],
                ],
                'status'     => [
                    'list'   => $statusList,
                    'entity' => [
                        'map' => $statusEntityTypes,
                    ],
                ],
            ];
        }
        if (isset($schema['scope']['user'])) {
            $schema['user'] = [
//                'fields' => $this->client->call('user.fields'),
                'fields' => [
                    'ID'          => [
                        'type'        => 'int',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    // special fields
                    'NAME_SEARCH' => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'IS_ONLINE'   => [
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
                    'USER_TYPE'   => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'ACTIVE'      => [
                        'type'        => 'char',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'NAME'        => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'LAST_NAME'   => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'SECOND_NAME' => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'EMAIL' => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    'WORK_COMPANY' => [
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
                    'WORK_PHONE' => [
                        'type'        => 'string',
                        'isReadOnly'  => true,
                        'isImmutable' => true,
                        'isMultiple'  => false,
                    ],
                    // TODO: describe the rest of the fields
//                    <PERSONAL_GENDER>Пол</PERSONAL_GENDER>
//                    <PERSONAL_PROFESSION>Профессия</PERSONAL_PROFESSION>
//                    <PERSONAL_WWW>Домашняя страничка</PERSONAL_WWW>
//                    <PERSONAL_BIRTHDAY>Дата рождения</PERSONAL_BIRTHDAY>
//                    <PERSONAL_PHOTO>Фотография</PERSONAL_PHOTO>
//                    <PERSONAL_ICQ>ICQ</PERSONAL_ICQ>
//                    <PERSONAL_PHONE>Личный телефон</PERSONAL_PHONE>
//                    <PERSONAL_FAX>Факс</PERSONAL_FAX>
//                    <PERSONAL_MOBILE>Личный мобильный</PERSONAL_MOBILE>
//                    <PERSONAL_PAGER>Пейджер</PERSONAL_PAGER>
//                    <PERSONAL_STREET>Улица проживания</PERSONAL_STREET>
//                    <PERSONAL_CITY>Город проживания</PERSONAL_CITY>
//                    <PERSONAL_STATE>Область / край</PERSONAL_STATE>
//                    <PERSONAL_ZIP>Почтовый индекс</PERSONAL_ZIP>
//                    <PERSONAL_COUNTRY>Страна</PERSONAL_COUNTRY>
//                    <UF_DEPARTMENT>Подразделения</UF_DEPARTMENT>
//                    <UF_INTERESTS>Интересы</UF_INTERESTS>
//                    <UF_SKILLS>Навыки</UF_SKILLS>
//                    <UF_WEB_SITES>Другие сайты</UF_WEB_SITES>
//                    <UF_XING>Xing</UF_XING>
//                    <UF_LINKEDIN>LinkedIn</UF_LINKEDIN>
//                    <UF_FACEBOOK>Facebook</UF_FACEBOOK>
//                    <UF_TWITTER>Twitter</UF_TWITTER>
//                    <UF_SKYPE>Skype</UF_SKYPE>
//                    <UF_DISTRICT>Район</UF_DISTRICT>
//                    <UF_PHONE_INNER>Внутренний телефон</UF_PHONE_INNER>
                ],
            ];
        }
        if (isset($schema['scope']['department'])) {
            $schema['department'] = [
//                'department' => $this->client->call('department.fields'),
                // TODO: determine mechanisms for core entities mutation control
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

    function assertInScope(string $scope): void
    {
        $scopes = $this->getSchema()['scope'];
        if (!isset($scopes[$scope])) {
            throw new BitrixClientException("Current schema has no access to scope '$scope', available: " .
                join(", ", $scopes));
        }
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
            case 'bool':
                return $value === true || $value === false;
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
     * @param array             $schema
     * @param GenericListFilter $filter
     * @param bool              $withConditionals
     * @throws BitrixException
     * @throws InputValidationException
     * @throws TransportException
     */
    function assertValidFilter(array $schema, GenericListFilter $filter, bool $withConditionals = true): void
    {
        foreach ($filter->getOrder() as $field => $value) {
            if (!($value === 'ASC' || $value === 'DESC')) {
                throw new InputValidationException("Filter order values must be either 'ASC' or 'DESC'");
            }
            if (empty($schema[$field])) {
                throw new InputValidationException("In filter order field '$field' does not exist");
            }
            if ($schema[$field]['type'] === 'crm_multifield') {
                throw new InputValidationException("In filter order multi-fields like '$field' are not allowed");
            }
        }
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
        self::assertValidFilterStart($filter->getStart());
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

    static function assertValidFilterStart(int $start)
    {
        if ($start < 0 || $start % 50 != 0) {
            throw new InputValidationException("Filter start must be a positive multiple of 50");
        }
    }

    function listResponseInbound(GenericListFilter $filter, array $listResponse)
    {
        if ($filter->getStart() > $listResponse['total']) {
            throw new NotFoundException("Filter reached out of list bounds");
        }
        if (isset($listResponse['next'])) {
            if ($listResponse['next'] != ($filter->getStart() + 50)) {
                throw new NotFoundException("Filter reached out of list bounds");
            }
        }
        // TODO: more checks??
    }
}