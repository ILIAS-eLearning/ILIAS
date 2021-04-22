<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\NoneFileNamePolicy;

/**
 * Class ConsumerFactory
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ConsumerFactory
{

    /**
     * @var StorageHandlerFactory
     */
    private $storage_handler_factory;
    /**
     * @var FileNamePolicy
     */
    protected $file_name_policy;

    /**
     * ConsumerFactory constructor.
     * @param StorageHandlerFactory $storage_handler_factory
     * @param FileNamePolicy|null   $file_name_policy
     */
    public function __construct(
        StorageHandlerFactory $storage_handler_factory,
        FileNamePolicy $file_name_policy = null
    ) {
        $this->storage_handler_factory = $storage_handler_factory;
        $this->file_name_policy = $file_name_policy ?? new NoneFileNamePolicy();
    }

    /**
     * @param StorableResource $resource
     * @return DownloadConsumer
     */
    public function download(StorableResource $resource) : DownloadConsumer
    {
        return new DownloadConsumer(
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource),
            $this->file_name_policy
        );
    }

    /**
     * @param StorableResource $resource
     * @return InlineConsumer
     */
    public function inline(StorableResource $resource) : InlineConsumer
    {
        return new InlineConsumer(
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource),
            $this->file_name_policy
        );
    }

    /**
     * @param StorableResource $resource
     * @return FileStreamConsumer
     */
    public function fileStream(StorableResource $resource) : FileStreamConsumer
    {
        return new FileStreamConsumer(
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource)
        );
    }

    /**
     * @param StorableResource $resource
     * @return AbsolutePathConsumer
     * @deprecated
     */
    public function absolutePath(StorableResource $resource) : AbsolutePathConsumer
    {
        return new AbsolutePathConsumer(
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource),
            $this->file_name_policy
        );
    }

    /**
     * @param StorableResource $resource
     * @return SrcConsumer
     */
    public function src(StorableResource $resource) : SrcConsumer
    {
        return new SrcConsumer(
            $resource,
            $this->storage_handler_factory->getHandlerForResource($resource)
        );
    }
}
