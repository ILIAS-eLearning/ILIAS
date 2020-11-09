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

    /**
     * @return \ILIAS\ResourceStorage\Revision\Revision|null
     */
    protected function getRevision() : \ILIAS\ResourceStorage\Revision\Revision
    {
        if ($this->revision_number !== null) {
            if ($this->resource->hasSpecificRevision($this->revision_number)) {
                $revision = $this->resource->getSpecificRevision($this->revision_number);
            } else {
                throw new \OutOfBoundsException("there is no version $this->revision_number of resource {$this->resource->getIdentification()->serialize()}");
            }
        } else {
            $revision = $this->resource->getCurrentRevision();
        }
        return $revision;
    }
}
