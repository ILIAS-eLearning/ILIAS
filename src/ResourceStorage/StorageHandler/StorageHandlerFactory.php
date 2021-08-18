<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler;

use ILIAS\ResourceStorage\Resource\StorableResource;

/**
 * Class StorageHandlerFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StorageHandlerFactory
{
    public const BASE_DIRECTORY = "storage";
    /**
     * @var StorageHandler[]
     */
    protected $handlers = [];
    /**
     * @var StorageHandler
     */
    protected $primary;

    /**
     * StorageHandlerFactory constructor.
     * @param StorageHandler[] $handlers
     */
    public function __construct(array $handlers)
    {
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getID()] = $handler;
            if ($handler->isPrimary()) {
                if ($this->primary !== null) {
                    throw new \LogicException("Only one primary StorageHandler can exist");
                }
                $this->primary = $handler;
            }
        }
        if ($this->primary === null) {
            throw new \LogicException("One primary StorageHandler must exist");
        }
    }

    /**
     * @param StorableResource $resource
     * @return StorageHandler
     */
    public function getHandlerForResource(StorableResource $resource) : StorageHandler
    {
        return $this->getHandlerForStorageId($resource->getStorageID());
    }

    public function getHandlerForStorageId(string $storage_id) : StorageHandler
    {
        if (isset($this->handlers[$storage_id])) {
            return $this->handlers[$storage_id];
        }

        throw new \LogicException("no other StorageHandler possible at the moment");
    }

    public function getPrimary() : StorageHandler
    {
        return $this->primary;
    }
}
