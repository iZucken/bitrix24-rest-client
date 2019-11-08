<?php

namespace bitrix\storage;

/**
 * Basic storage fallback
 *
 * @package bitrix\storage
 */
class InMemoryMap implements Storage
{
    private $prefix;
    public $storage = [];

    public function __construct(array $initial, string $prefix)
    {
        $this->storage = $initial;
        $this->prefix = $prefix;
    }

    public function set(string $key, $value)
    {
        $this->storage[$this->prefix . $key] = $value;
    }

    public function get(string $key)
    {
        return $this->storage[$this->prefix . $key] ?? null;
    }
}