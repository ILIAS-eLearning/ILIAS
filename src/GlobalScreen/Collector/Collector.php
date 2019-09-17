<?php

namespace ILIAS\GlobalScreen\Collector;

/**
 * Interface Collector
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Collector
{

    /**
     * Runs the Collection of all items from the providers
     */
    public function collect() : void;


    /**
     * @return array
     */
    public function getItems() : array;


    /**
     * @return bool
     *
     * @throws LogicException if collect() has not been run first
     */
    public function hasItems() : bool;
}
