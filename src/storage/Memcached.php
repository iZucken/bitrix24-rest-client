<?php

namespace bitrix\storage;

use Psr\Log\LoggerInterface;

/**
 * Simple Memcached storage
 *
 * @package bitrix\storage
 */
class Memcached implements Storage
{
    /**
     * @var \Memcached
     */
    private $cache;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(\Memcached $memcached, string $prefix, LoggerInterface $logger)
    {
        $this->prefix = $prefix;
        $this->logger = $logger;
        $this->cache = $memcached;
    }

    public function set(string $key, $value)
    {
        $this->logger->debug("Writing cache $this->prefix$key");
        $set = $this->cache->set($this->prefix . $key, $value, 86400);
        if ($set === false) {
            $this->logger->warning("Failed to write cache $this->prefix$key");
        }
    }

    public function get(string $key)
    {
        $this->logger->debug("Reading cache $this->prefix$key");
        $value = $this->cache->get($this->prefix . $key);
        if ($this->cache->getResultCode() === $this->cache::RES_NOTFOUND) {
            $this->logger->info("Cache miss $this->prefix$key");
        }
        return $value;
    }
}