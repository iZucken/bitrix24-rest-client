<?php


namespace bitrix\rest\client;

use bitrix\exception\BitrixClientException;
use bitrix\exception\BitrixServerException;
use bitrix\exception\TransportException;
use bitrix\exception\UndefinedBitrixServerException;
use bitrix\storage\Storage;

/**
 * Makes client-side scope access checks
 *
 * @package bitrix\rest\client
 */
class ScopeCheckDecorator implements BitrixClient
{
    /**
     * @var BitrixClient
     */
    public $bitrix;
    public $pulled = false;
    public $scopeAll = null;
    public $scopePermitted = null;
    /**
     * @var Storage
     */
    private $storage;

    const SCOPE_ALL = "ScopeAll";
    const SCOPE_PERMITTED = "ScopePermitted";

    function __construct(BitrixClient $bitrix, Storage $storage)
    {
        $this->bitrix = $bitrix;
        $this->storage = $storage;
    }

    public function info(): string
    {
        return $this->bitrix->info() . " with scope checks";
    }

    public function purge()
    {
        $this->storage->set(self::SCOPE_ALL, null);
        $this->storage->set(self::SCOPE_PERMITTED, null);
    }

    public function call(string $method, array $parameters = [])
    {
        $this->pullScope();
        if (!isset($this->scopeAll[$method])) {
            throw new BitrixClientException("Method $method does not exist");
        }
        if (!isset($this->scopePermitted[$method])) {
            throw new BitrixClientException("Method $method is outside of connection scope");
        }
        return $this->bitrix->call($method, $parameters);
    }

    /**
     * @throws BitrixClientException
     * @throws BitrixServerException
     * @throws TransportException
     * @throws UndefinedBitrixServerException
     */
    public function pullScope()
    {
        if (!$this->pulled) {
            $this->scopeAll = $this->storage->get(self::SCOPE_ALL);
            $this->scopePermitted = $this->storage->get(self::SCOPE_PERMITTED);
            if (empty($this->scopeAll) || empty($this->scopePermitted)) {
                $scope = $this->bitrix->call("methods");
                $this->scopePermitted = array_combine(array_values($scope), array_fill(0, count($scope), true));
                $scope = $this->bitrix->call("methods", ["FULL" => true]);
                $this->scopeAll = array_combine(array_values($scope), array_fill(0, count($scope), true));
                $this->storage->set(self::SCOPE_PERMITTED, $this->scopePermitted);
                $this->storage->set(self::SCOPE_ALL, $this->scopeAll);
            }
            $this->pulled = true;
        }
    }
}