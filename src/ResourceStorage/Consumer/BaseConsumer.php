<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;

/**
 * Class BaseConsumer
 * @package ILIAS\ResourceStorage\Consumer
 */
abstract class BaseConsumer implements DeliveryConsumer
{
    use GetRevisionTrait;

    /**
     * @var StorageHandler
     */
    protected $storage_handler;
    /**
     * @var StorableResource
     */
    protected $resource;
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

    abstract public function run() : void;

    /**
     * @inheritDoc
     */
    public function setRevisionNumber(int $revision_number) : DeliveryConsumer
    {
        $this->revision_number = $revision_number;
        return $this;
    }

}
