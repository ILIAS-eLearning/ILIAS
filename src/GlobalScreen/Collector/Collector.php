<?php declare(strict_types=1);

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
     *
     * @deprecated
     */
    public function collectOnce() : void;


    /**
     * @return bool
     */
    public function hasBeenCollected() : bool;
}
