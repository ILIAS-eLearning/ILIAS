<?php namespace ILIAS\GlobalScreen\Collector;

use ilDBInterface;
use ilGlobalCache;

/**
 * Class CoreStorageFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class CoreStorageFacade implements StorageFacade
{

    /**
     * @return ilGlobalCache
     */
    public function cache() : ilGlobalCache
    {
        return ilGlobalCache::getInstance(ilGlobalCache::COMP_GLOBAL_SCREEN);
    }


    /**
     * @return ilDBInterface
     */
    public function db() : ilDBInterface
    {
        global $DIC;

        return $DIC->database();
    }
}
