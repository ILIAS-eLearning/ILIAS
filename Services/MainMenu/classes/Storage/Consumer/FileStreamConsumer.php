<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\Consumer;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\MainMenu\Storage\StorableResource;
use ILIAS\MainMenu\Storage\StorageHandler\StorageHandler;

/**
 * Class FileStreamConsumer
 *
 * @package ILIAS\MainMenu\Storage\Consumer
 */
class FileStreamConsumer implements StreamConsumer
{

    /**
     * @var StorageHandler
     */
    private $storage_handler;
    /**
     * @var StorableResource
     */
    private $resource;


    /**
     * DownloadConsumer constructor.
     *
     * @param StorableResource $resource
     * @param StorageHandler   $storage_handler
     */
    public function __construct(StorableResource $resource, StorageHandler $storage_handler)
    {
        $this->resource = $resource;
        $this->storage_handler = $storage_handler;
    }


    public function getStream() : FileStream
    {
        $revision = $this->resource->getCurrentRevision();

        return $this->storage_handler->getStream($revision);
    }
}
