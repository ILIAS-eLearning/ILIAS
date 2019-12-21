<?php

/**
 * Interface ilBiblTableQueryInfoInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTableQueryInfoInterface
{
    const SORTING_ASC = "ASC";
    const SORTING_DESC = "DESC";


    /**
     * @return string
     */
    public function getSortingColumn();


    /**
     * @param string $sorting_column
     */
    public function setSortingColumn($sorting_column);


    /**
     * @return string
     */
    public function getSortingDirection();


    /**
     * @param string $sorting_direction
     */
    public function setSortingDirection($sorting_direction);


    /**
     * @return int
     */
    public function getOffset();


    /**
     * @param int $offset
     */
    public function setOffset($offset);


    /**
     * @return int
     */
    public function getLimit();


    /**
     * @param int $limit
     */
    public function setLimit($limit);


    /**
     * @param \ilBiblTableQueryFilterInterface $filter
     *
     * @return void
     */
    public function addFilter(ilBiblTableQueryFilterInterface $filter);


    /**
     * @return \ilBiblTableQueryFilterInterface[]
     */
    public function getFilters();
}
