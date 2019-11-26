<?php


namespace bitrix\endpoint;


use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\InputValidationException;
use bitrix\exception\NotFoundException;
use bitrix\exception\TransportException;
use bitrix\exception\UndefinedBitrixServerException;
use bitrix\Utility;

/**
 * Wrapper for legacy user and group related CRUD methods
 *
 * @package bitrix\endpoint
 */
abstract class UserDepartmentLegacyCrud extends CommonCrud
{
    function get(int $id): array
    {
        $result = $this->schema->client->call($this->getScopePath() . ".get", ["ID" => $id]);
        if (empty($result['result'])) {
            throw new NotFoundException("Entity not found");
        }
        if (isset($result['total']) && $result['total'] > 1) {
            throw new UndefinedBitrixServerException("Somehow entity ID is ambiguous.");
        }
        return $result['result'][0];
    }

    function update(int $id, array $fields): bool
    {
        $this->schema->assertValidFields($this->getScopeSettings()['fields'], $fields, true);
        $fields["ID"] = $id;
        try {
            return $this->schema->client->call($this->getScopePath() . '.update', $fields);
        } catch (BitrixServerException $exception) {
            throw $this->convertNotFoundException($exception);
        }
    }

    /**
     * @param GenericListFilter $filter
     * @return array
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws NotFoundException
     * @throws UndefinedBitrixServerException
     * @throws BitrixException
     * @throws InputValidationException
     * @throws TransportException
     */
    function list(GenericListFilter $filter): array
    {
        $this->schema->assertValidFilter($this->getScopeSettings()['fields'], $filter, false);
        $data = [
            'start'      => $filter->getStart(),
            'ADMIN_MODE' => true,
        ];
        $order = $filter->getOrder();
        if (!empty($order)) {
            if (count($filter->getOrder()) > 1) {
                throw new BitrixClientException("This type of entity can only be sorted by one field");
            } else {
                reset($order);
                $data['sort'] = key($order);
                $data['order'] = $order[$data['sort']];
            }
        }
        $list = $this->schema->client->call($this->getScopePath() . '.get', array_merge($filter->getFilter(), $data));
        if (empty($list['result']) || empty($list['total'])) {
            $list['result'] = [];
            $list['total'] = 0;
        }
        $this->schema->assertListResponseInbound($filter, $list);
        $select = $filter->getSelect();
        if (!empty($select)) {
            $omits = array_diff(array_keys($this->getScopeSettings()['fields']), $select);
            foreach ($omits as $omit) {
                $list['result'] = Utility::unsetColumn($list['result'], $omit);
            }
        }
        return $list;
    }

    function delete(int $id): bool
    {
        throw new BitrixClientException(__CLASS__." restricts deletion"); // TODO: it is actually supported, just determine control flow
    }
}