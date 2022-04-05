<?php

/**
 * Interface ilBiblTableQueryInfoInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblTableQueryInfoInterface
{
    public const SORTING_ASC = "ASC";
    public const SORTING_DESC = "DESC";
    
    public function getSortingColumn() : string;
    
    public function setSortingColumn(string $sorting_column) : void;
    
    public function getSortingDirection() : string;
    
    public function setSortingDirection(string $sorting_direction) : void;
    
    public function getOffset() : int;
    
    public function setOffset(int $offset) : void;
    
    public function getLimit() : int;
    
    public function setLimit(int $limit) : void;
    
    public function addFilter(ilBiblTableQueryFilterInterface $filter) : void;
    
    /**
     * @return \ilBiblTableQueryFilterInterface[]
     */
    public function getFilters() : array;
}
