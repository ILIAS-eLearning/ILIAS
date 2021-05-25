<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;

/**
 * Class FileStreamConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class FileStreamConsumer implements StreamConsumer
{
    use GetRevisionTrait;

    /**
     * @var StorageHandler
     */
    private $storage_handler;
    /**
     * @var StorableResource
     */
    private $resource;
    /**
     * @var int|null
     */
    protected $revision_number = null;

    /**
     * DownloadConsumer constructor.
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
        $revision = $this->getRevision();

        return $this->storage_handler->getStream($revision);
    }

    /**
     * @inheritDoc
     */
    public function setRevisionNumber(int $revision_number) : FileStreamConsumer
    {
        $this->revision_number = $revision_number;
        return $this;
    }

}
