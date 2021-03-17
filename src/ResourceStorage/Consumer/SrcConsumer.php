<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;

/**
 * Class SrcConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
class SrcConsumer
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
    protected $revision_number;

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

    public function getSrc() : string
    {
        $revision = $this->getRevision();
        $stream = $this->storage_handler->getStream($revision);
        $base64 = base64_encode($stream->getContents());
        $mime = $revision->getInformation()->getMimeType();

        return "data:{$mime};base64,{$base64}";
    }

    /**
     * @inheritDoc
     */
    public function setRevisionNumber(int $revision_number) : SrcConsumer
    {
        $this->revision_number = $revision_number;
        return $this;
    }

}
