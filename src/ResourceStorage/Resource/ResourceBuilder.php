<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource;

use Generator;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Repository\InformationRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceRepository;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\Revision\Repository\RevisionRepository;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Consumer\FileStreamConsumer;
use ILIAS\ResourceStorage\Revision\CloneRevision;
use ILIAS\ResourceStorage\Resource\InfoResolver\InfoResolver;
use ILIAS\ResourceStorage\Revision\FileRevision;
use ILIAS\ResourceStorage\Resource\InfoResolver\ClonedRevisionInfoResolver;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\NoneFileNamePolicy;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Class ResourceBuilder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class ResourceBuilder
{

    /**
     * @var InformationRepository
     */
    private $information_repository;
    /**
     * @var ResourceRepository
     */
    private $resource_repository;
    /**
     * @var RevisionRepository
     */
    private $revision_repository;
    /**
     * @var StorageHandlerFactory
     */
    private $storage_handler_factory;
    /**
     * @var StakeholderRepository
     */
    private $stakeholder_repository;
    /**
     * @var LockHandler
     */
    private $lock_handler;
    /**
     * @var StorableResource[]
     */
    protected $resource_cache = [];
    /**
     * @var FileNamePolicy
     */
    protected $file_name_policy;
    /**
     * @var StorageHandler
     */
    protected $primary_storage_handler;

    /**
     * ResourceBuilder constructor.
     * @param StorageHandlerFactory $storage_handler_factory
     * @param RevisionRepository    $revision_repository
     * @param ResourceRepository    $resource_repository
     * @param InformationRepository $information_repository
     * @param StakeholderRepository $stakeholder_repository
     * @param LockHandler           $lock_handler
     * @param FileNamePolicy|null   $file_name_policy
     */
    public function __construct(
        StorageHandlerFactory $storage_handler_factory,
        RevisionRepository $revision_repository,
        ResourceRepository $resource_repository,
        InformationRepository $information_repository,
        StakeholderRepository $stakeholder_repository,
        LockHandler $lock_handler,
        FileNamePolicy $file_name_policy = null
    ) {
        $this->storage_handler_factory = $storage_handler_factory;
        $this->primary_storage_handler = $storage_handler_factory->getPrimary();
        $this->revision_repository = $revision_repository;
        $this->resource_repository = $resource_repository;
        $this->information_repository = $information_repository;
        $this->stakeholder_repository = $stakeholder_repository;
        $this->lock_handler = $lock_handler;
        $this->file_name_policy = $file_name_policy ?? new NoneFileNamePolicy();
    }

    //
    // Methods to create new Resources (from an Upload, a Stream od just a blank one)
    //
    /**
     * @inheritDoc
     */
    public function new(
        UploadResult $result,
        InfoResolver $info_resolver
    ) : StorableResource {
        $resource = $this->resource_repository->blank($this->primary_storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());

        return $this->append($resource, $result, $info_resolver);
    }

    public function newFromStream(
        FileStream $stream,
        InfoResolver $info_resolver,
        bool $keep_original = false
    ) : StorableResource {
        $resource = $this->resource_repository->blank($this->primary_storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());

        return $this->appendFromStream($resource, $stream, $info_resolver, $keep_original);
    }

    public function newBlank() : StorableResource
    {
        $resource = $this->resource_repository->blank($this->primary_storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());
        $resource->setStorageID($this->primary_storage_handler->getID());

        return $resource;
    }

    //
    // Methods to append something to an existing resource
    //

    public function append(
        StorableResource $resource,
        UploadResult $result,
        InfoResolver $info_resolver
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromUpload($info_resolver, $resource, $result);
        $revision = $this->populateRevisionInfo($revision, $info_resolver);

        $resource->addRevision($revision);
        $resource->setStorageID($resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID());

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function replaceWithUpload(
        StorableResource $resource,
        UploadResult $result,
        InfoResolver $info_resolver
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromUpload($info_resolver, $resource, $result);
        $revision = $this->populateRevisionInfo($revision, $info_resolver);

        foreach ($resource->getAllRevisions() as $existing_revision) {
            $this->deleteRevision($resource, $existing_revision);
        }

        $resource->addRevision($revision);
        $resource->setStorageID($resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID());

        return $resource;
    }

    public function appendFromStream(
        StorableResource $resource,
        FileStream $stream,
        InfoResolver $info_resolver,
        bool $keep_original = false
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromStream($info_resolver, $resource, $stream, $keep_original);
        $revision = $this->populateRevisionInfo($revision, $info_resolver);

        $resource->addRevision($revision);
        $resource->setStorageID($resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID());

        return $resource;
    }

    public function replaceWithStream(
        StorableResource $resource,
        FileStream $stream,
        InfoResolver $info_resolver,
        bool $keep_original = false
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromStream($info_resolver, $resource, $stream, $keep_original);
        $revision = $this->populateRevisionInfo($revision, $info_resolver);

        foreach ($resource->getAllRevisions() as $existing_revision) {
            $this->deleteRevision($resource, $existing_revision);
        }

        $resource->addRevision($revision);
        $resource->setStorageID($resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID());

        return $resource;
    }

    public function appendFromRevision(
        StorableResource $resource,
        int $revision_number
    ) : StorableResource {
        $existing_revision = $resource->getSpecificRevision($revision_number);
        if ($existing_revision instanceof FileRevision) {
            $info_resolver = new ClonedRevisionInfoResolver(
                $resource->getMaxRevision() + 1,
                $existing_revision
            );

            $cloned_revision = $this->revision_repository->blankFromClone(
                $info_resolver,
                $resource,
                $existing_revision
            );

            $this->populateRevisionInfo($cloned_revision, $info_resolver);

            $resource->addRevision($cloned_revision);
            $resource->setStorageID($resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID());
            return $resource;
        }
        return $resource;

    }

    /**
     * @param ResourceIdentification $identification
     * @return bool
     * @description check if a resource exists
     */
    public function has(ResourceIdentification $identification) : bool
    {
        return $this->resource_repository->has($identification);
    }

    /**
     * @param StorableResource $resource
     * @description after you have modified a resource, you can store it here
     * @throws \ILIAS\ResourceStorage\Policy\FileNamePolicyException
     */
    public function store(StorableResource $resource) : void
    {
        foreach ($resource->getAllRevisions() as $revision) {
            $this->file_name_policy->check($revision->getInformation()->getSuffix());
        }

        $r = $this->lock_handler->lockTables(array_merge(
            $this->resource_repository->getNamesForLocking(),
            $this->revision_repository->getNamesForLocking(),
            $this->information_repository->getNamesForLocking(),
            $this->stakeholder_repository->getNamesForLocking(),

        ), function () use ($resource) {
            $this->resource_repository->store($resource);

            foreach ($resource->getAllRevisions() as $revision) {
                $this->storeRevision($revision);
            }

            foreach ($resource->getStakeholders() as $stakeholder) {
                $this->stakeholder_repository->register($resource->getIdentification(), $stakeholder);
            }
        });

        $r->runAndUnlock();
    }

    /**
     * @param StorableResource $resource
     * @return StorableResource
     * @description Clone anexisting resource with all it's revisions, stakeholders and information
     */
    public function clone(StorableResource $resource) : StorableResource
    {
        $new_resource = $this->newBlank();
        foreach ($resource->getStakeholders() as $stakeholder) {
            $stakeholder = clone $stakeholder;
            $new_resource->addStakeholder($stakeholder);
        }

        foreach ($resource->getAllRevisions() as $existing_revision) {
            if (!$existing_revision instanceof FileRevision) {
                continue;
            }
            $info_resolver = new ClonedRevisionInfoResolver(
                $existing_revision->getVersionNumber(),
                $existing_revision
            );

            $stream = new FileStreamConsumer($resource, $this->primary_storage_handler);
            $stream->setRevisionNumber($existing_revision->getVersionNumber());

            $cloned_revision = new FileStreamRevision($new_resource->getIdentification(), $stream->getStream(), true);
            $this->populateRevisionInfo($cloned_revision, $info_resolver);
            $cloned_revision->setVersionNumber($existing_revision->getVersionNumber());

            $new_resource->addRevision($cloned_revision);
        }
        $this->store($new_resource);
        return $new_resource;

    }

    /**
     * @description  Store one Revision
     * @param Revision $revision
     * @throws \ILIAS\ResourceStorage\Policy\FileNamePolicyException
     */
    public function storeRevision(Revision $revision) : void
    {
        if ($revision instanceof UploadedFileRevision) {
            // check policies
            $this->file_name_policy->check($revision->getInformation()->getSuffix());
            $this->primary_storage_handler->storeUpload($revision);
        }
        if ($revision instanceof FileStreamRevision) {
            $this->primary_storage_handler->storeStream($revision);
        }
        if ($revision instanceof CloneRevision) {
            $this->primary_storage_handler->cloneRevision($revision);
        }
        $this->revision_repository->store($revision);
        $this->information_repository->store($revision->getInformation(), $revision);
    }

    /**
     * @param ResourceIdentification $identification
     * @return StorableResource
     * @throws ResourceNotFoundException
     * @description Get a Resource out of a Identification
     */
    public function get(ResourceIdentification $identification) : StorableResource
    {
        if (isset($this->resource_cache[$identification->serialize()])) {
            return $this->resource_cache[$identification->serialize()];
        }
        $resource = $this->resource_repository->get($identification);

        $this->resource_cache[$identification->serialize()] = $this->populateNakedResourceWithRevisionsAndStakeholders($resource);

        return $this->resource_cache[$identification->serialize()];
    }

    /**
     * @description Reve a complete revision. if there are other Stakeholder, only your stakeholder gets removed
     * @param StorableResource         $resource
     * @param ResourceStakeholder|null $stakeholder
     * @return bool whether ResourceStakeholders handled this successful
     */
    public function remove(StorableResource $resource, ResourceStakeholder $stakeholder = null) : bool
    {
        $sucessful = true;
        if ($stakeholder instanceof ResourceStakeholder) {
            $this->stakeholder_repository->deregister($resource->getIdentification(), $stakeholder);
            $sucessful = $sucessful && $stakeholder->resourceHasBeenDeleted($resource->getIdentification());
            $resource->removeStakeholder($stakeholder);
            if (count($resource->getStakeholders()) > 0) {
                return $sucessful;
            }
        }
        foreach ($resource->getStakeholders() as $s) {
            $sucessful = $sucessful && $s->resourceHasBeenDeleted($resource->getIdentification());
        }

        foreach ($resource->getAllRevisions() as $revision) {
            $this->deleteRevision($resource, $revision);
        }

        $this->storage_handler_factory->getHandlerForResource($resource)->deleteResource($resource);
        $this->resource_repository->delete($resource);

        return $sucessful;
    }

    public function removeRevision(StorableResource $resource, int $revision_number) : void
    {
        $reveision_to_delete = $resource->getSpecificRevision($revision_number);
        if ($reveision_to_delete) {
            $this->deleteRevision($resource, $reveision_to_delete);
        }
        $this->store($resource);
    }

    private function deleteRevision(StorableResource $resource, Revision $revision) : void
    {
        try {
            $this->storage_handler_factory->getHandlerForResource($resource)->deleteRevision($revision);
        } catch (\Throwable $t) {
        }

        $this->information_repository->delete($revision->getInformation(), $revision);
        $this->revision_repository->delete($revision);
        $resource->removeRevision($revision);
    }

    /**
     * @return Generator
     */
    public function getAll() : Generator
    {
        /**
         * @var $resource StorableResource
         */
        foreach ($this->resource_repository->getAll() as $resource) {
            yield $this->populateNakedResourceWithRevisionsAndStakeholders($resource);
        }
    }

    /**
     * @param StorableResource $resource
     * @return StorableResource
     */
    private function populateNakedResourceWithRevisionsAndStakeholders(StorableResource $resource) : StorableResource
    {
        $revisions = $this->revision_repository->get($resource);
        $resource->setRevisions($revisions);

        foreach ($resource->getAllRevisions() as $revision) {
            $information = $this->information_repository->get($revision);
            $revision->setInformation($information);
        }

        foreach ($this->stakeholder_repository->getStakeholders($resource->getIdentification()) as $s) {
            $resource->addStakeholder($s);
        }

        return $resource;
    }

    private function populateRevisionInfo(Revision $revision, InfoResolver $info_resolver) : Revision
    {
        $info = $revision->getInformation();

        $info->setTitle($info_resolver->getFileName());
        $info->setMimeType($info_resolver->getMimeType());
        $info->setSuffix($info_resolver->getSuffix());
        $info->setSize($info_resolver->getSize());
        $info->setCreationDate($info_resolver->getCreationDate());

        $revision->setInformation($info);
        $revision->setTitle($info_resolver->getRevisionTitle());
        $revision->setOwnerId($info_resolver->getOwnerId());

        return $revision;
    }
}
