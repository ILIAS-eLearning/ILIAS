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
    protected $filters = [];
    /**
     * @var string
     */
    protected $sorting_column = '';
    /**
     * @var string
     */
    protected $sorting_direction = ilBiblTableQueryInfoInterface::SORTING_ASC;
    /**
     * @var int
     */
    protected $offset = 0;
    /**
     * @var int
     */
    protected $limit = 10000;


    /**
     * @return string
     */
    public function getSortingColumn()
    {
        return $this->sorting_column;
    }


    /**
     * @param string $sorting_column
     */
    public function setSortingColumn($sorting_column)
    {
        $this->sorting_column = $sorting_column;
    }


    /**
     * @return string
     */
    public function getSortingDirection()
    {
        return $this->sorting_direction;
    }


    /**
     * @param string $sorting_direction
     */
    public function setSortingDirection($sorting_direction)
    {
        $this->sorting_direction = $sorting_direction;
    }


    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }


    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }


    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }


    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }


    /**
     * @inheritDoc
     */
    public function addFilter(ilBiblTableQueryFilterInterface $filter)
    {
        $this->filters[] = $filter;
    }


    /**
     * @inheritDoc
     */
    public function getFilters()
    {
        return $this->filters;
    }
}
