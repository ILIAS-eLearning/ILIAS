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

    /**
     * @inheritDoc
     */
    public function new(UploadResult $result) : StorableResource
    {
        $resource = $this->resource_repository->blank($this->storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());

        return $this->append($resource, $result);
    }

    public function newFromStream(FileStream $stream, bool $keep_original = false) : StorableResource
    {
        $resource = $this->resource_repository->blank($this->storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());

        return $this->appendFromStream($resource, $stream, $keep_original);
    }

    public function newBlank() : StorableResource
    {
        $resource = $this->resource_repository->blank($this->storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function append(
        StorableResource $resource,
        UploadResult $result,
        string $revision_title = null
    ) : StorableResource {
        $revision = $this->revision_repository->blank($resource, $result);

        $info = $revision->getInformation();
        $info->setTitle($result->getName());
        $info->setMimeType($result->getMimeType());
        $info->setSize($result->getSize());
        $info->setCreationDate(new \DateTimeImmutable());

        $revision->setInformation($info);
        $revision->setTitle($revision_title ?? $result->getName());

        $resource->addRevision($revision);
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    public function appendFromStream(
        StorableResource $resource,
        FileStream $stream,
        bool $keep_original = false,
        string $revision_title = null
    ) : StorableResource {
        $revision = $this->revision_repository->blankFromStream($resource, $stream, $keep_original);
        $path = $stream->getMetadata('uri');
        $file_name = basename($path);
        $info = $revision->getInformation();
        $info->setTitle($file_name);
        $info->setMimeType(mime_content_type($path));
        $info->setSize($stream->getSize());
        $info->setCreationDate(new \DateTimeImmutable());

        $revision->setTitle($revision_title ?? $file_name);
        $resource->addRevision($revision);
        $resource->setStorageID($this->storage_handler->getID());

        return $resource;
    }

    public function has(ResourceIdentification $identification) : bool
    {
        return $this->resource_repository->has($identification) && $this->storage_handler->has($identification);
    }

    /**
     * @param StorableResource $resource
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
                if ($revision instanceof UploadedFileRevision) {
                    $this->storage_handler->storeUpload($revision);
                }
                if ($revision instanceof FileStreamRevision) {
                    $this->storage_handler->storeStream($revision);
                }
                $this->revision_repository->store($revision);
                $this->information_repository->store($revision->getInformation(), $revision);
            }

            foreach ($resource->getStakeholders() as $stakeholder) {
                $this->stakeholder_repository->register($resource->getIdentification(), $stakeholder);
            }
        });

        $r->runAndUnlock();
    }

    /**
     * @inheritDoc
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

    /**
     * @param StorableResource $resource
     */
    public function remove(StorableResource $resource) : void
    {
        foreach ($resource->getAllRevisions() as $revision) {
            $this->information_repository->delete($revision->getInformation(), $revision);
            $this->revision_repository->delete($revision);
        }
        $this->storage_handler->deleteResource($resource);
        $this->resource_repository->delete($resource);
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
}
