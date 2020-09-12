<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage;

use Generator;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Consumer\ConsumerFactory;
use ILIAS\ResourceStorage\Consumer\DownloadConsumer;
use ILIAS\ResourceStorage\Consumer\FileStreamConsumer;
use ILIAS\ResourceStorage\Consumer\InlineConsumer;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Repository\InformationARRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceARRepository;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Resource\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Revision\Repository\RevisionARRepository;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\StorageHandler\FileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use LogicException;

/**
 * Class Services
 *
 * @public
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Services
{

    /**
     * @var ConsumerFactory
     */
    private $consumer_factory;
    /**
     * @var ResourceBuilder
     */
    private $resource_builder;


    /**
     * Services constructor.
     */
    public function __construct()
    {
        $this->resource_builder = new ResourceBuilder(
            new FileSystemStorageHandler(),
            new RevisionARRepository(),
            new ResourceARRepository(),
            new InformationARRepository()
        );
        $this->consumer_factory = new ConsumerFactory(new StorageHandlerFactory());
    }


    /**
     * this is the fast-lane: in most cases you want to store a uploaded file in
     * the storage and use it's identification.
     *
     * @param UploadResult        $result
     * @param ResourceStakeholder $stakeholder
     * @param string              $title
     *
     * @return ResourceIdentification
     */
    public function upload(UploadResult $result, ResourceStakeholder $stakeholder, string $title = null) : ResourceIdentification
    {
        if ($result->isOK()) {
            $resource = $this->resource_builder->new($result);

            $this->resource_builder->store($resource);

            return $resource->getIdentification();
        } else {
            throw new LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
        }
    }


    public function find(string $identification) : ?ResourceIdentification
    {
        $resource_identification = new ResourceIdentification($identification);

        if ($this->resource_builder->has($resource_identification)) {
            return $resource_identification;
        }

        return null;
    }


    public function getRevision(ResourceIdentification $identification) : Revision
    {
        return $this->resource_builder->get($identification)->getCurrentRevision();
    }


    public function remove(ResourceIdentification $identification) : void
    {
        $this->resource_builder->remove($this->resource_builder->get($identification));
    }


    /**
     * @return Generator|ResourceIdentification[]
     */
    public function getAll() : Generator
    {
        foreach ($this->resource_builder->getAll() as $item) {
            /**
             * @var $item StorableResource
             */
            yield $item->getIdentification();
        }
    }


    //
    // CONSUMERS
    //

    public function download(ResourceIdentification $identification) : DownloadConsumer
    {
        return $this->consumer_factory->download($this->resource_builder->get($identification));
    }


    public function inline(ResourceIdentification $identification) : InlineConsumer
    {
        return $this->consumer_factory->inline($this->resource_builder->get($identification));
    }


    public function stream(ResourceIdentification $identification) : FileStreamConsumer
    {
        return $this->consumer_factory->fileStream($this->resource_builder->get($identification));
    }
}
