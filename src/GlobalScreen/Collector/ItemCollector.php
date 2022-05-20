<?php declare(strict_types=1);

namespace ILIAS\GlobalScreen\Collector;

use LogicException;

/**
 * Interface Collector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ItemCollector extends Collector
{
    public function collectStructure() : void;


    public function filterItemsByVisibilty(bool $async_only = false) : void;


    public function prepareItemsForUIRepresentation() : void;


    /**
     * @return \Generator
     */
    public function getItemsForUIRepresentation() : \Generator;


    /**
     * @return bool
     *
     * @throws LogicException if collectOnce() has not been run first
     */
    public function hasItems() : bool;
    
    public function hasVisibleItems() : bool;
}
