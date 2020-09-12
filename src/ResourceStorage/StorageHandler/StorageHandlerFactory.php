<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler;

use ILIAS\ResourceStorage\StorableResource;

/**
 * Class StorageHandlerFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StorageHandlerFactory
{

    /**
     * @param StorableResource $resource
     *
     * @return StorageHandler
     */
    public function getHandlerForResource(StorableResource $resource) : StorageHandler
    {
        $file_system_storage_handler = new FileSystemStorageHandler();
        switch ($resource->getStorageID()) {
            case $file_system_storage_handler->getID():

                return $file_system_storage_handler;
            // More to come
            default:
                throw new \LogicException("no other StorageHandler possible at the moment");
        }
    }
}
