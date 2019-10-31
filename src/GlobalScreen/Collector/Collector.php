<?php

namespace ILIAS\GlobalScreen\Collector;

/**
 * Interface Collector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Collector
{

    public function collectStructure() : void;


    public function filterItemsByVisibilty() : void;


    public function prepareItemsForUIRepresentation() : void;


    /**
     * Runs the Collection of all items from the providers
     *
     * @deprecated
     */
    public function collect() : void;


    /**
     * @return array
     */
    public function getItemsForUITranslation() : array;


    /**
     * @return bool
     *
     * @throws LogicException if collect() has not been run first
     */
    public function hasItems() : bool;
}
