<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;

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
     * @var FileNamePolicy
     */
    protected $file_name_policy;

    /**
     * DownloadConsumer constructor.
     * @param StorableResource $resource
     * @param StorageHandler   $storage_handler
     * @param FileNamePolicy   $file_name_policy
     */
    public function __construct(
        StorableResource $resource,
        StorageHandler $storage_handler,
        FileNamePolicy $file_name_policy
    )
    {
        $this->resource = $resource;
        $this->storage_handler = $storage_handler;
        $this->file_name_policy = $file_name_policy;
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
