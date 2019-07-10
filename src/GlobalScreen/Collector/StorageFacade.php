<?php namespace ILIAS\GlobalScreen\Collector;

use ilDBInterface;
use ilGlobalCache;

/**
 * Class StorageFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StorageFacade
{

    /**
     * @return ilGlobalCache
     */
    public function cache() : ilGlobalCache;


    /**
     * @return ilDBInterface
     */
    public function db() : ilDBInterface;
}
