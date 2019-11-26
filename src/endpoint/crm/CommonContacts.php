<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\Schema;
use bitrix\exception\BitrixException;
use bitrix\exception\InputValidationException;
use bitrix\exception\TransportException;
use bitrix\Utility;

/**
 * Provides ability to look for some types of CRM entities that share common contacts
 *
 * @package endpoint\crm
 */
class CommonContacts
{
    const CONTACT_TYPE_PHONE = 'PHONE';
    const CONTACT_TYPE_EMAIL = 'EMAIL';

    const ENTITY_LEAD = 'LEAD';
    const ENTITY_CONTACT = 'CONTACT';
    const ENTITY_COMPANY = 'COMPANY';

    /**
     * @var Schema
     */
    private $schema;

    function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param array       $items
     * @param string      $contactType
     * @param string|null $entityType
     * @return array [ 'LEAD' => [...int], 'CONTACT' => [...int], 'COMPANY' => [...int] ]
     * @throws InputValidationException
     * @throws BitrixException
     * @throws TransportException
     */
    function find(array $items, string $contactType, string $entityType = null): array
    {
        if (!($contactType === self::CONTACT_TYPE_PHONE || $contactType === self::CONTACT_TYPE_EMAIL)) {
            throw new InputValidationException("Invalid contact type");
        }
        if (isset($entityType) && !(
                $entityType === self::ENTITY_LEAD ||
                $entityType === self::ENTITY_CONTACT ||
                $entityType === self::ENTITY_COMPANY
            )) {
            throw new InputValidationException("Invalid entity type");
        }
        if (count($items) > 20) {
            throw new InputValidationException("Too many items provided for common entities search");
        }
        if (!Utility::isPlainArray($items)) {
            throw new InputValidationException("Items must be in a plain array");
        }
        foreach ($items as $item) {
            if (!is_string($item)) {
                throw new InputValidationException("Items must only be strings");
            }
        }
        $data = [
            'TYPE'   => $contactType,
            'VALUES' => $items,
        ];
        if (isset($entityType)) {
            $data['ENTITY_TYPE'] = $entityType;
        }
        $result = $this->schema->client->call("crm.duplicate.findbycomm", $data);
        return [
            self::ENTITY_LEAD    => $result[self::ENTITY_LEAD] ?? [],
            self::ENTITY_CONTACT => $result[self::ENTITY_CONTACT] ?? [],
            self::ENTITY_COMPANY => $result[self::ENTITY_COMPANY] ?? [],
        ];
    }
}