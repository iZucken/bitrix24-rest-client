<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;

class User extends UserDepartmentLegacyCrud
{
    function getScopeSettings(): array
    {
        return $this->schema->getSchema()['user'];
    }

    function getScopeName(): string
    {
        return "user";
    }

    function getScopePath(): string
    {
        return "user";
    }

    function delete(int $id): bool
    {
        throw new BitrixClientException("User deletion is not supported");
    }
}