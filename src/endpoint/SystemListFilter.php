<?php


namespace bitrix\endpoint;

/**
 * Simple container for system list method filters
 *
 * @package bitrix\endpoint
 */
class SystemListFilter
{
    /**
     * @var array
     */
    protected $filter;
    /**
     * @var array
     */
    protected $order;

    public function __construct(
        array $filter = [],
        array $order = []
    ) {
        $this->filter = $filter;
        $this->order = $order;
    }

    function getOrder(): array
    {
        return $this->order;
    }

    function getFilter(): array
    {
        return $this->filter;
    }

    function filter(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    function order(array $order): self
    {
        $this->order = $order;
        return $this;
    }

    function toFullMap(): array
    {
        return [
            'ORDER'  => $this->order,
            'FILTER' => $this->filter,
        ];
    }

    /**
     * @param array $map
     * @return SystemListFilter
     */
    static function fromFullMap(array $map): SystemListFilter
    {
        return new SystemListFilter(
            $map['FILTER'],
            $map['ORDER']
        );
    }

    /**
     * @param array $map
     * @return SystemListFilter
     */
    static function fromWeakMap(array $map): SystemListFilter
    {
        return new SystemListFilter(
            $map['FILTER'] ?? $map['filter'] ?? [],
            $map['ORDER'] ?? $map['order'] ?? []
        );
    }
}