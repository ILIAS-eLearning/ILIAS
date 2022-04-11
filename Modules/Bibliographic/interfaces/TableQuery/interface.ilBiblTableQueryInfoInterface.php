<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
