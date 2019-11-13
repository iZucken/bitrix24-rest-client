<?php


namespace bitrix\endpoint;

use bitrix\exception\InputValidationException;

/**
 * Simple container for generic list method filters
 *
 * @package bitrix\endpoint
 */
class GenericListFilter
{
    /**
     * @var array
     */
    protected $filter;
    /**
     * @var array
     */
    protected $select;
    /**
     * @var array
     */
    protected $order;
    /**
     * @var int
     */
    protected $start;

    /**
     * GenericListFilter constructor.
     *
     * @param array $filter
     * @param array $select
     * @param array $order
     * @param int   $start
     * @throws InputValidationException
     */
    public function __construct(
        array $filter = [],
        array $select = [],
        array $order = [],
        int $start = 0
    ) {
        Schema::assertValidFilterStart($start);
        $this->filter = $filter;
        $this->select = $select;
        $this->order = $order;
        $this->start = $start;
    }

    function getOrder(): array
    {
        return $this->order;
    }

    function getSelect(): array
    {
        return $this->select;
    }

    function getFilter(): array
    {
        return $this->filter;
    }

    function getStart(): int
    {
        return $this->start;
    }

    function filter(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    function select(array $select): self
    {
        $this->select = $select;
        return $this;
    }

    function order(array $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param int $from
     * @return $this
     * @throws InputValidationException
     */
    function start(int $from): self
    {
        Schema::assertValidFilterStart($from);
        return $this;
    }

    function toFullMap(): array
    {
        return [
            'ORDER'  => $this->order,
            'SELECT' => $this->select,
            'FILTER' => $this->filter,
            'start'  => $this->start,
        ];
    }

    /**
     * @param array $map
     * @return GenericListFilter
     * @throws InputValidationException
     */
    static function fromFullMap(array $map): GenericListFilter
    {
        return new GenericListFilter(
            $map['FILTER'],
            $map['SELECT'],
            $map['ORDER'],
            $map['start']
        );
    }

    /**
     * @param array $map
     * @return GenericListFilter
     * @throws InputValidationException
     */
    static function fromWeakMap(array $map): GenericListFilter
    {
        return new GenericListFilter(
            $map['FILTER'] ?? $map['filter'] ?? [],
            $map['SELECT'] ?? $map['select'] ?? [],
            $map['ORDER'] ?? $map['order'] ?? [],
            $map['START'] ?? $map['start'] ?? 0
        );
    }
}