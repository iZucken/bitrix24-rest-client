<?php


namespace bitrix\endpoint\crm;


use bitrix\endpoint\CrmCrud;
use bitrix\endpoint\GenericListFilter;
use bitrix\exception\BitrixException;
use bitrix\exception\InputValidationException;
use bitrix\exception\NotFoundException;
use bitrix\exception\TransportException;

/**
 * Wraps related CRM CRUD methods
 *
 * @package endpoint
 *
 * crm.product.fields
 * crm.product.property.fields
 * crm.product.property.types
 * crm.product.property.settings.fields
 * crm.product.property.enumeration.fields
 */
class Product extends CrmCrud
{
    function getScopeName(): string
    {
        return "crm";
    }

    function getScopePath(): string
    {
        return 'crm.product';
    }

    /**
     * @return array
     * @throws BitrixException
     * @throws TransportException
     */
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['crm']['product'];
    }

    public function add(array $fields): int
    {
        return parent::add($this->remapFields($fields));
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $fields): bool
    {
        return parent::update($id, $this->remapFields($fields));
    }

    /**
     * @inheritDoc
     */
    public function list(GenericListFilter $filter): array
    {
        $filter->filter($this->remapFields($filter->getFilter()));
        $filter->select($this->remapFieldNames($filter->getSelect()));
        $filter->order($this->remapFieldNames($filter->getOrder()));
        return parent::list($filter);
    }

    public function remapFieldNames($fields)
    {
        $map = $this->getScopeSettings()['fieldsMap'];
        $remapped = [];
        foreach ($fields as $field) {
            $remapped [] = $map[$field] ?? $field;
        }
        return $remapped;
    }

    public function remapFields($fields)
    {
        $map = $this->getScopeSettings()['fieldsMap'];
        $remapped = [];
        foreach ($fields as $field => $value) {
            $key = $map[$field] ?? $field;
            $remapped[$key] = $value;
        }
        return $remapped;
    }
}