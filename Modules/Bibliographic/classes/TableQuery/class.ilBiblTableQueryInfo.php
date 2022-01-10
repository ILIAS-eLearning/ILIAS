<?php

/**
 * Class ilBiblTableQueryInfo
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBiblTableQueryInfo implements ilBiblTableQueryInfoInterface
{

    /**
     * @var \ilBiblTableQueryFilterInterface[]
     */
    protected array $filters = [];
    protected string $sorting_column = '';
    protected string $sorting_direction = ilBiblTableQueryInfoInterface::SORTING_ASC;
    protected int $offset = 0;
    protected int $limit = 10000;


    /**
     * @return string
     */
    public function getSortingColumn() : string
    {
        return $this->sorting_column;
    }


    /**
     * @param string $sorting_column
     */
    public function setSortingColumn(string $sorting_column) : void
    {
        $this->sorting_column = $sorting_column;
    }


    /**
     * @return string
     */
    public function getSortingDirection() : string
    {
        return $this->sorting_direction;
    }


    /**
     * @param string $sorting_direction
     */
    public function setSortingDirection(string $sorting_direction) : void
    {
        $this->sorting_direction = $sorting_direction;
    }


    /**
     * @return int
     */
    public function getOffset() : int
    {
        return $this->offset;
    }


    /**
     * @param int $offset
     */
    public function setOffset(int $offset) : void
    {
        $this->offset = $offset;
    }


    /**
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }


    /**
     * @param int $limit
     */
    public function setLimit(int $limit) : void
    {
        $this->limit = $limit;
    }


    /**
     * @inheritDoc
     */
    public function addFilter(ilBiblTableQueryFilterInterface $filter) : void
    {
        $this->filters[] = $filter;
    }


    /**
     * @inheritDoc
     */
    public function getFilters() : array
    {
        return $this->filters;
    }
}
