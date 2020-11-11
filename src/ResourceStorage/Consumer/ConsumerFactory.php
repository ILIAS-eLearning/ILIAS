<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Class ConsumerFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ConsumerFactory
{

    /**
     * @var StorageHandlerFactory
     */
    private $storage_handler_factory;


    /**
     * ConsumerFactory constructor.
     *
     * @param StorageHandlerFactory $storage_handler_factory
     */
    public function __construct(StorageHandlerFactory $storage_handler_factory)
    {
        $this->storage_handler_factory = $storage_handler_factory;
    }


    /**
     * @param StorableResource $resource
     *
     * @return DownloadConsumer
     */
    public function download(StorableResource $resource) : DownloadConsumer
    {
        return new DownloadConsumer($resource, $this->storage_handler_factory->getHandlerForResource($resource));
    }


    /**
     * @param StorableResource $resource
     *
     * @return InlineConsumer
     */
    public function inline(StorableResource $resource) : InlineConsumer
    {
        return new InlineConsumer($resource, $this->storage_handler_factory->getHandlerForResource($resource));
    }


    /**
     * @param StorableResource $resource
     *
     * @return FileStreamConsumer
     */
    public function fileStream(StorableResource $resource) : FileStreamConsumer
    {
        return new FileStreamConsumer($resource, $this->storage_handler_factory->getHandlerForResource($resource));
    }
}
