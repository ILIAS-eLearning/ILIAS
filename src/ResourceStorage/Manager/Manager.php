<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Manager;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\StorableResource;

/**
 * Class StorageManager
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Manager
{
    /**
     * @var ResourceBuilder
     */
    protected $resource_builder;

    /**
     * Manager constructor.
     * @param ResourceBuilder $b
     */
    public function __construct(
        ResourceBuilder $b
    ) {
        $this->resource_builder = $b;
    }

    /**
     * this is the fast-lane: in most cases you want to store a uploaded file in
     * the storage and use it's identification.
     * @param UploadResult        $result
     * @param ResourceStakeholder $stakeholder
     * @param string|null         $title
     * @return ResourceIdentification
     */
    public function upload(UploadResult $result, ResourceStakeholder $stakeholder, string $title = null) : ResourceIdentification
    {
        if ($result->isOK()) {
            $resource = $this->resource_builder->new($result);
            $resource->addStakeholder($stakeholder);

            $this->resource_builder->store($resource);

            return $resource->getIdentification();
        }
        throw new \LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
    }

    public function find(string $identification) : ?ResourceIdentification
    {
        $resource_identification = new ResourceIdentification($identification);

        if ($this->resource_builder->has($resource_identification)) {
            return $resource_identification;
        }

        return null;
    }

    public function remove(ResourceIdentification $identification) : void
    {
        $this->resource_builder->remove($this->resource_builder->get($identification));
    }

    public function appendNewVersion(ResourceIdentification $identification, UploadResult $result, ResourceStakeholder $stakeholder) : StorableResource
    {
        if ($result->isOK()) {
            if (!$this->resource_builder->has($identification)) {
                throw new \LogicException("Resource not found, can't append new version in: " . $identification->serialize());
            }

            $resource = $this->resource_builder->get($identification);
            $revision = $this->resource_builder->append($resource, $result);
            $resource->addStakeholder($stakeholder);

            $this->resource_builder->store($resource);

            return $revision;
        }
        throw new \LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
    }

    public function getRevision(ResourceIdentification $identification) : Revision
    {
        return $this->resource_builder->get($identification)->getCurrentRevision();
    }

    /**
     * @return Generator|ResourceIdentification[]
     */
    public function getAll() : \Generator
    {
        foreach ($this->resource_builder->getAll() as $item) {
            /**
             * @var $item StorableResource
             */
            yield $item->getIdentification();
        }
    }
}
