<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Manager;

use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\Filesystem\Stream\FileStream;

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


    // Identifications

    /**
     * this is the fast-lane: in most cases you want to store a uploaded file in
     * the storage and use it's identification.
     * @param UploadResult        $result
     * @param ResourceStakeholder $stakeholder
     * @param string|null         $title
     * @return ResourceIdentification
     */
    public function upload(
        UploadResult $result,
        ResourceStakeholder $stakeholder,
        string $title = null
    ) : ResourceIdentification {
        if ($result->isOK()) {
            $resource = $this->resource_builder->new(
                $result,
                $title,
                $stakeholder->getOwnerOfNewResources()
            );
            $resource->addStakeholder($stakeholder);
            $this->resource_builder->store($resource);

            return $resource->getIdentification();
        }
        throw new \LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
    }

    public function stream(
        FileStream $stream,
        ResourceStakeholder $stakeholder,
        string $title = null
    ) : ResourceIdentification {
        $resource = $this->resource_builder->newFromStream(
            $stream,
            true,
            $title,
            $stakeholder->getOwnerOfNewResources()
        );
        $resource->addStakeholder($stakeholder);
        $this->resource_builder->store($resource);

        return $resource->getIdentification();
    }

    public function find(string $identification) : ?ResourceIdentification
    {
        $resource_identification = new ResourceIdentification($identification);

        if ($this->resource_builder->has($resource_identification)) {
            return $resource_identification;
        }

        return null;
    }

    // Resources

    public function getResource(ResourceIdentification $i) : StorableResource
    {
        return $this->resource_builder->get($i);
    }

    public function remove(ResourceIdentification $identification, ResourceStakeholder $stakeholder) : void
    {
        $this->resource_builder->remove($this->resource_builder->get($identification), $stakeholder);
    }

    public function clone(ResourceIdentification $identification) : ResourceIdentification
    {
        $resource = $this->resource_builder->clone($this->resource_builder->get($identification));

        return $resource->getIdentification();
    }

    // Revision

    public function appendNewRevision(
        ResourceIdentification $identification,
        UploadResult $result,
        ResourceStakeholder $stakeholder,
        string $revision_title = null
    ) : Revision {
        if ($result->isOK()) {
            if (!$this->resource_builder->has($identification)) {
                throw new \LogicException("Resource not found, can't append new version in: " . $identification->serialize());
            }

            $resource = $this->resource_builder->get($identification);
            $this->resource_builder->append(
                $resource,
                $result,
                $revision_title,
                $stakeholder->getOwnerOfNewResources()
            );
            $resource->addStakeholder($stakeholder);

            $this->resource_builder->store($resource);

            return $resource->getCurrentRevision();
        }
        throw new \LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
    }

    public function replaceWithUpload(
        ResourceIdentification $identification,
        UploadResult $result,
        ResourceStakeholder $stakeholder,
        string $revision_title = null
    ) : Revision {
        if ($result->isOK()) {
            if (!$this->resource_builder->has($identification)) {
                throw new \LogicException("Resource not found, can't append new version in: " . $identification->serialize());
            }

            $resource = $this->resource_builder->get($identification);
            $this->resource_builder->replaceWithUpload(
                $resource,
                $result,
                $revision_title,
                $stakeholder->getOwnerOfNewResources()
            );
            $resource->addStakeholder($stakeholder);

            $this->resource_builder->store($resource);

            return $resource->getCurrentRevision();
        }
        throw new \LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
    }

    public function appendNewRevisionFromStream(
        ResourceIdentification $identification,
        FileStream $stream,
        ResourceStakeholder $stakeholder,
        string $revision_title = null
    ) : Revision {
        if (!$this->resource_builder->has($identification)) {
            throw new \LogicException("Resource not found, can't append new version in: " . $identification->serialize());
        }

        $resource = $this->resource_builder->get($identification);
        $this->resource_builder->appendFromStream(
            $resource,
            $stream,
            true,
            $revision_title,
            $stakeholder->getOwnerOfNewResources()
        );
        $resource->addStakeholder($stakeholder);

        $this->resource_builder->store($resource);

        return $resource->getCurrentRevision();
    }

    public function replaceWithStream(
        ResourceIdentification $identification,
        FileStream $stream,
        ResourceStakeholder $stakeholder,
        string $revision_title = null
    ) : Revision {
        if (!$this->resource_builder->has($identification)) {
            throw new \LogicException("Resource not found, can't append new version in: " . $identification->serialize());
        }

        $resource = $this->resource_builder->get($identification);
        $this->resource_builder->replaceWithStream(
            $resource,
            $stream,
            true,
            $revision_title,
            $stakeholder->getOwnerOfNewResources()
        );
        $resource->addStakeholder($stakeholder);

        $this->resource_builder->store($resource);

        return $resource->getCurrentRevision();
    }

    public function getCurrentRevision(ResourceIdentification $identification) : Revision
    {
        return $this->resource_builder->get($identification)->getCurrentRevision();
    }

    public function updateRevision(Revision $revision) : bool
    {
        $this->resource_builder->storeRevision($revision);

        return true;
    }

    public function rollbackRevision(ResourceIdentification $identification, int $revision_number) : bool
    {
        $resource = $this->resource_builder->get($identification);
        $this->resource_builder->appendFromRevision($resource, $revision_number);
        $this->resource_builder->store($resource);

        return true;
    }

    public function reduceToRevision(ResourceIdentification $identification, int $revision_number) : bool
    {
        return true;
    }

}
