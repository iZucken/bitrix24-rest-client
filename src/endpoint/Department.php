<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;

class Department extends UserDepartmentLegacyCrud
{
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['department'];
    }

    function getScopeName(): string
    {
        return "department";
    }

    function getScopePath(): string
    {
        return "department";
    }

    function delete(int $id): bool
    {
        throw new BitrixClientException("User deletion is not supported"); // TODO: it is actually supported, just determine control flow
    }
}