<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler;

use ILIAS\ResourceStorage\StorableResource;

/**
 * Class StorageHandlerFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StorageHandlerFactory
{
    /**
     * @var StorageHandler[]
     */
    protected $handlers = [];

    /**
     * StorageHandlerFactory constructor.
     * @param StorageHandler[] $handlers
     */
    public function __construct(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getID()] = $handler;
        }
    }

    /**
     * @param StorableResource $resource
     * @return StorageHandler
     */
    public function getHandlerForResource(StorableResource $resource) : StorageHandler
    {
        if (isset($this->handlers[$resource->getStorageID()])) {
            return $this->handlers[$resource->getStorageID()];
        }

        throw new \LogicException("no other StorageHandler possible at the moment");

    }
}
