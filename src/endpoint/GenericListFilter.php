<?php


namespace bitrix\endpoint;

/**
 * Simple container for generic list method filters
 *
 * TODO: move some of the validation into this class?
 *
 * @package bitrxi\endpoint
 */
class GenericListFilter
{
    /**
     * @var array
     */
    private $filter;
    /**
     * @var array
     */
    private $select;
    /**
     * @var array
     */
    private $order;
    /**
     * @var int
     */
    private $start;

    public function __construct(
        array $filter = [],
        array $select = [],
        array $order = [],
        int $start = 0
    ) {
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

    function toMap(): array
    {
        return [
            'ORDER'  => $this->order,
            'SELECT' => $this->select,
            'FILTER' => $this->filter,
            'start'  => $this->start,
        ];
    }

    static function fromFullMap(array $map): GenericListFilter
    {
        return new GenericListFilter(
            $map['FILTER'],
            $map['SELECT'],
            $map['ORDER'],
            $map['start']
        );
    }
}