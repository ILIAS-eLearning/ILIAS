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

/**
 * Class ResourceBuilder
 * @internal
 * @author Fabian Schmid <fs@studer-raimann.ch>
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
     * @var StorageHandler
     */
    private $storage_handler;
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
     * ResourceBuilder constructor.
     * @param StorageHandler        $storage_handler
     * @param RevisionRepository    $revision_repository
     * @param ResourceRepository    $resource_repository
     * @param InformationRepository $information_repository
     * @param StakeholderRepository $stakeholder_repository
     * @param LockHandler           $lock_handler
     */
    public function __construct(
        StorageHandler $storage_handler,
        RevisionRepository $revision_repository,
        ResourceRepository $resource_repository,
        InformationRepository $information_repository,
        StakeholderRepository $stakeholder_repository,
        LockHandler $lock_handler
    ) {
        $this->storage_handler = $storage_handler;
        $this->revision_repository = $revision_repository;
        $this->resource_repository = $resource_repository;
        $this->information_repository = $information_repository;
        $this->stakeholder_repository = $stakeholder_repository;
        $this->lock_handler = $lock_handler;
    }

    //
    // Methods to create new Resources (from an Upload, a Stream od just a blank one)
    //
    /**
     * @inheritDoc
     */
    public function new(UploadResult $result, string $title = null, int $owner_id = null) : StorableResource
    {
        $resource = $this->resource_repository->blank($this->storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());

        return $this->append($resource, $result, $title, $owner_id);
    }

    public function newFromStream(
        FileStream $stream,
        bool $keep_original = false,
        string $title = null,
        int $ownner_id = null
    ) : StorableResource {
        $resource = $this->resource_repository->blank($this->storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());

        return $this->appendFromStream($resource, $stream, $keep_original, $title, $ownner_id);
    }

    public function newBlank() : StorableResource
    {
        $resource = $this->resource_repository->blank($this->storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    //
    // Methods to append something to an existing resource
    //

    /**
     * @inheritDoc
     */
    public function append(
        StorableResource $resource,
        UploadResult $result,
        string $revision_title = null,
        int $owner_id = null
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromUpload($resource, $result);

        $info = $revision->getInformation();
        $info->setTitle($result->getName());
        $info->setMimeType($result->getMimeType());
        $info->setSuffix(pathinfo($result->getName(), PATHINFO_EXTENSION));
        $info->setSize($result->getSize());
        $info->setCreationDate(new \DateTimeImmutable());

        $revision->setInformation($info);
        $revision->setTitle($revision_title ?? $result->getName());
        $revision->setOwnerId($owner_id ?? 6);

        $resource->addRevision($revision);
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function replaceWithUpload(
        StorableResource $resource,
        UploadResult $result,
        string $revision_title = null,
        int $owner_id = null
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromUpload($resource, $result);

        $info = $revision->getInformation();
        $info->setTitle($result->getName());
        $info->setMimeType($result->getMimeType());
        $info->setSuffix(pathinfo($result->getName(), PATHINFO_EXTENSION));
        $info->setSize($result->getSize());
        $info->setCreationDate(new \DateTimeImmutable());

        $revision->setInformation($info);
        $revision->setTitle($revision_title ?? $result->getName());
        $revision->setOwnerId($owner_id ?? 6);

        foreach ($resource->getAllRevisions() as $existing_revision) {
            $this->deleteRevision($resource, $existing_revision);
        }

        $resource->addRevision($revision);
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    public function appendFromStream(
        StorableResource $resource,
        FileStream $stream,
        bool $keep_original = false,
        string $revision_title = null,
        int $owner_id = null
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromStream($resource, $stream, $keep_original);
        $info = $revision->getInformation();
        $path = $stream->getMetadata('uri');
        if ($path && $path !== 'php://input') {
            $file_name = basename($path);
            $info->setTitle($file_name);
            $info->setSuffix(pathinfo($file_name, PATHINFO_EXTENSION));
            $info->setMimeType(function_exists('mime_content_type') ? mime_content_type($path) : 'unknown');
            $info->setSize($stream->getSize());
            $filectime = filectime($path);
            $info->setCreationDate($filectime ? (new \DateTimeImmutable())->setTimestamp($filectime) : new \DateTimeImmutable());
        }

        $revision->setOwnerId($owner_id ?? 6);
        $revision->setTitle($revision_title ?? $file_name ?? 'stream');
        $resource->addRevision($revision);
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    /**
     * @param StorableResource $resource
     * @param FileStream       $stream
     * @param bool             $keep_original
     * @param string|null      $revision_title
     * @param int|null         $owner_id
     * @return StorableResource
     */
    public function replaceWithStream(
        StorableResource $resource,
        FileStream $stream,
        bool $keep_original = false,
        string $revision_title = null,
        int $owner_id = null
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromStream($resource, $stream, $keep_original);
        $info = $revision->getInformation();
        $path = $stream->getMetadata('uri');
        if ($path && $path !== 'php://input') {
            $file_name = basename($path);
            $info->setTitle($file_name);
            $info->setSuffix(pathinfo($file_name, PATHINFO_EXTENSION));
            $info->setMimeType(function_exists('mime_content_type') ? mime_content_type($path) : 'unknown');
            $info->setSize($stream->getSize());
            $filectime = filectime($path);
            $info->setCreationDate($filectime ? (new \DateTimeImmutable())->setTimestamp($filectime) : new \DateTimeImmutable());
        }

        $revision->setOwnerId($owner_id ?? 6);
        $revision->setTitle($revision_title ?? $file_name ?? 'stream');

        foreach ($resource->getAllRevisions() as $existing_revision) {
            $this->deleteRevision($resource, $existing_revision);
        }

        $resource->addRevision($revision);
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    public function appendFromRevision(
        StorableResource $resource,
        int $revision_number,
        int $owner_id = null
    ) : StorableResource {
        $existing_revision = $resource->getSpecificRevision($revision_number);
        $existing_revision_info = $existing_revision->getInformation();
        $cloned_revision = $this->revision_repository->blankFromClone($resource, $revision_number);
        $cloned_revision_info = $cloned_revision->getInformation();

        $cloned_revision_info->setTitle($existing_revision_info->getTitle());
        $cloned_revision_info->setSuffix($existing_revision_info->getSuffix());
        $cloned_revision_info->setMimeType($existing_revision_info->getMimeType());
        $cloned_revision_info->setSize($existing_revision_info->getSize());
        $cloned_revision_info->setCreationDate($existing_revision_info->getCreationDate());

        $cloned_revision->setTitle($existing_revision->getTitle());
        $cloned_revision->setOwnerId($owner_id ?? 6);

        $resource->addRevision($cloned_revision);
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    /**
     * @param ResourceIdentification $identification
     * @return bool
     * @description check if a resource exists
     */
    public function has(ResourceIdentification $identification) : bool
    {
        return $this->resource_repository->has($identification) && $this->storage_handler->has($identification);
    }

    /**
     * @param StorableResource $resource
     * @description after you have modified a resource, you can store it here
     */
    public function store(StorableResource $resource) : void
    {
        $r = $this->lock_handler->lockTables([
            $this->resource_repository->getNameForLocking(),
            $this->revision_repository->getNameForLocking(),
            $this->information_repository->getNameForLocking(),
            $this->stakeholder_repository->getNameForLocking(),

        ], function () use ($resource) {
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

        foreach ($resource->getAllRevisions() as $revision) {
            $stream = new FileStreamConsumer($resource, $this->storage_handler);
            $stream->setRevisionNumber($revision->getVersionNumber());
            $cloned_revision = new FileStreamRevision($new_resource->getIdentification(), $stream->getStream(), true);
            $cloned_revision->setTitle($revision->getTitle());
            $cloned_revision->setOwnerId($revision->getOwnerId());
            $cloned_revision->setVersionNumber($revision->getVersionNumber());
            $cloned_revision->setInformation($revision->getInformation());
            $new_resource->addRevision($cloned_revision);
        }
        $this->store($new_resource);
        return $new_resource;

    }

    /**
     * @description  Store one Revision
     * @param Revision $revision
     */
    public function storeRevision(Revision $revision) : void
    {
        if ($revision instanceof UploadedFileRevision) {
            $this->storage_handler->storeUpload($revision);
        }
        if ($revision instanceof FileStreamRevision) {
            $this->storage_handler->storeStream($revision);
        }
        if ($revision instanceof CloneRevision) {
            $this->storage_handler->cloneRevision($revision);
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
     * @param StorableResource    $resource
     * @param ResourceStakeholder $stakeholder
     */
    public function remove(StorableResource $resource, ResourceStakeholder $stakeholder) : void
    {
        $this->stakeholder_repository->deregister($resource->getIdentification(), $stakeholder);
        if (count($resource->getStakeholders()) > 1) {
            return;
        }

        foreach ($resource->getAllRevisions() as $revision) {
            $this->deleteRevision($resource, $revision);
        }
        $this->storage_handler->deleteResource($resource);
        $this->resource_repository->delete($resource);
    }

    private function deleteRevision(StorableResource $resource, Revision $revision) : void
    {
        $this->storage_handler->deleteRevision($revision);
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
}
