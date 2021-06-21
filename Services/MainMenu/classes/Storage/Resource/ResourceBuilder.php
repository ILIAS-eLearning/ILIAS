<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Storage\Resource;

use Generator;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\MainMenu\Storage\Identification\ResourceIdentification;
use ILIAS\MainMenu\Storage\Information\Repository\InformationRepository;
use ILIAS\MainMenu\Storage\Resource\Repository\ResourceRepository;
use ILIAS\MainMenu\Storage\Revision\Repository\RevisionRepository;
use ILIAS\MainMenu\Storage\Revision\UploadedFileRevision;
use ILIAS\MainMenu\Storage\StorableResource;
use ILIAS\MainMenu\Storage\StorageHandler\StorageHandler;

/**
 * Class ResourceBuilder
 *
 * @internal
 *
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
     * ResourceBuilder constructor.
     *
     * @param StorageHandler        $storage_handler
     * @param RevisionRepository    $revision_repository
     * @param ResourceRepository    $resource_repository
     * @param InformationRepository $information_repository
     */
    public function __construct(StorageHandler $storage_handler, RevisionRepository $revision_repository, ResourceRepository $resource_repository, InformationRepository $information_repository)
    {
        $this->storage_handler = $storage_handler;
        $this->revision_repository = $revision_repository;
        $this->resource_repository = $resource_repository;
        $this->information_repository = $information_repository;
    }


    /**
     * @inheritDoc
     */
    public function new(UploadResult $result) : StorableResource
    {
        $resource = $this->resource_repository->blank($this->storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification());
        $revision = $this->revision_repository->blank($resource, $result);

        $info = $revision->getInformation();
        $info->setTitle($result->getName());
        $info->setMimeType($result->getMimeType());
        $info->setSize($result->getSize());
        $info->setCreationDate(new \DateTimeImmutable());
        $revision->setInformation($info);

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
        $this->resource_repository->store($resource);

        foreach ($resource->getAllRevisions() as $revision) {
            if ($revision instanceof UploadedFileRevision) {
                $this->storage_handler->storeUpload($revision);
            }
            $this->revision_repository->store($revision);
            $this->information_repository->store($revision->getInformation(), $revision);
        }
    }


    /**
     * @inheritDoc
     */
    public function get(ResourceIdentification $identification) : StorableResource
    {
        $resource = $this->resource_repository->get($identification);

        return $this->populateNakedResourceWithRevisionsAndStakeholders($resource);
    }


    /**
     * @param StorableResource $resource
     *
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
