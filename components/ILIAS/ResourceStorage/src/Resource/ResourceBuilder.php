<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Lock\LockHandler;
use ILIAS\ResourceStorage\Policy\FileNamePolicy;
use ILIAS\ResourceStorage\Policy\NoneFileNamePolicy;
use ILIAS\ResourceStorage\Preloader\SecureString;
use ILIAS\ResourceStorage\Repositories;
use ILIAS\ResourceStorage\Resource\InfoResolver\ClonedRevisionInfoResolver;
use ILIAS\ResourceStorage\Resource\InfoResolver\InfoResolver;
use ILIAS\ResourceStorage\Revision\CloneRevision;
use ILIAS\ResourceStorage\Revision\FileRevision;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\Revision\RevisionStatus;
use ILIAS\ResourceStorage\Revision\StreamReplacementRevision;
use ILIAS\ResourceStorage\StorageHandler\Migrator;

/**
 * Class ResourceBuilder
 * @author   Fabian Schmid <fabian@sr.solutions.ch>
 * @internal This class is not part of the public API and may be changed without notice. Do not use this class in your code.
 */
class ResourceBuilder
{
    use SecureString;

    /**
     * @readonly
     */
    private \ILIAS\ResourceStorage\Information\Repository\InformationRepository $information_repository;
    /**
     * @readonly
     */
    private \ILIAS\ResourceStorage\Resource\Repository\ResourceRepository $resource_repository;
    /**
     * @readonly
     */
    private \ILIAS\ResourceStorage\Revision\Repository\RevisionRepository $revision_repository;
    /**
     * @readonly
     */
    private \ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderRepository $stakeholder_repository;

    /**
     * @var StorableResource[]
     */
    protected array $resource_cache = [];
    protected \ILIAS\ResourceStorage\Policy\FileNamePolicy $file_name_policy;
    protected \ILIAS\ResourceStorage\StorageHandler\StorageHandler $primary_storage_handler;
    private StorageHandlerFactory $storage_handler_factory;
    private LockHandler $lock_handler;
    private StreamAccess $stream_access;
    private bool $auto_migrate = true;

    /**
     * ResourceBuilder constructor.
     * @param FileNamePolicy|null $file_name_policy
     */
    public function __construct(
        StorageHandlerFactory $storage_handler_factory,
        Repositories $repositories,
        LockHandler $lock_handler,
        StreamAccess $stream_access,
        FileNamePolicy $file_name_policy = null
    ) {
        $this->storage_handler_factory = $storage_handler_factory;
        $this->lock_handler = $lock_handler;
        $this->stream_access = $stream_access;
        $this->primary_storage_handler = $storage_handler_factory->getPrimary();
        $this->revision_repository = $repositories->getRevisionRepository();
        $this->resource_repository = $repositories->getResourceRepository();
        $this->information_repository = $repositories->getInformationRepository();
        $this->stakeholder_repository = $repositories->getStakeholderRepository();
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
        InfoResolver $info_resolver,
        ResourceType $type = ResourceType::SINGLE_FILE
    ): StorableResource {
        $resource = $this->resource_repository->blank(
            $this->primary_storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification(),
            $type
        );

        return $this->append($resource, $result, $info_resolver, RevisionStatus::PUBLISHED);
    }

    public function newFromStream(
        FileStream $stream,
        InfoResolver $info_resolver,
        bool $keep_original = false,
        ResourceType $type = ResourceType::SINGLE_FILE
    ): StorableResource {
        $resource = $this->resource_repository->blank(
            $this->primary_storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification(),
            $type
        );

        return $this->appendFromStream(
            $resource,
            $stream,
            $info_resolver,
            RevisionStatus::PUBLISHED,
            $keep_original
        );
    }

    public function newBlank(ResourceType $type = ResourceType::SINGLE_FILE): StorableResource
    {
        $resource = $this->resource_repository->blank(
            $this->primary_storage_handler->getIdentificationGenerator()->getUniqueResourceIdentification(),
            $type
        );
        $resource->setStorageID($this->primary_storage_handler->getID());

        return $resource;
    }

    //
    // Methods to append something to an existing resource
    //

    public function append(
        StorableResource $resource,
        UploadResult $result,
        InfoResolver $info_resolver,
        RevisionStatus $status
    ): StorableResource {
        if (
            $status !== RevisionStatus::DRAFT
            && $resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
            throw new \LogicException(
                'You can not replace a draft revision with a published, you must publish the current revision first'
            );
        }

        $new_revision = $this->revision_repository->blankFromUpload($info_resolver, $resource, $result, $status);

        if ($resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
            $clone_revision = $this->buildDraftReplacementRevision($resource, $new_revision, $info_resolver);
            $resource->replaceRevision($clone_revision);
        } else {
            $new_revision = $this->populateRevisionInfo($new_revision, $info_resolver);
            $resource->addRevision($new_revision);
        }

        $resource->setStorageID(
            $resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID()
        );

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function replaceWithUpload(
        StorableResource $resource,
        UploadResult $result,
        InfoResolver $info_resolver
    ): StorableResource {
        if ($resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
            throw new \LogicException('You can not replace a draft revision, you must publish it first');
        }
        $revision = $this->revision_repository->blankFromUpload(
            $info_resolver,
            $resource,
            $result,
            RevisionStatus::PUBLISHED
        );
        $revision = $this->populateRevisionInfo($revision, $info_resolver);

        foreach ($resource->getAllRevisionsIncludingDraft() as $existing_revision) {
            $this->deleteRevision($resource, $existing_revision);
        }

        $resource->addRevision($revision);
        $resource->setStorageID(
            $resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID()
        );

        return $resource;
    }

    public function appendFromStream(
        StorableResource $resource,
        FileStream $stream,
        InfoResolver $info_resolver,
        RevisionStatus $status,
        bool $keep_original = false,
    ): StorableResource {
        if (
            $status !== RevisionStatus::DRAFT
            && $resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
            throw new \LogicException(
                'You can not replace a draft revision with a published, you must publish the current revision first'
            );
        }

        $new_revision = $this->revision_repository->blankFromStream(
            $info_resolver,
            $resource,
            $stream,
            $status,
            $keep_original
        );

        if ($resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
            $clone_revision = $this->buildDraftReplacementRevision($resource, $new_revision, $info_resolver);
            $resource->replaceRevision($clone_revision);
        } else {
            $new_revision = $this->populateRevisionInfo($new_revision, $info_resolver);
            $resource->addRevision($new_revision);
        }

        $resource->setStorageID(
            $resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID()
        );

        return $resource;
    }

    public function replaceWithStream(
        StorableResource $resource,
        FileStream $stream,
        InfoResolver $info_resolver,
        bool $keep_original = false,
    ): StorableResource {
        if ($resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
            throw new \LogicException('You can not replace a draft revision, you must publish it first');
        }
        $revision = $this->revision_repository->blankFromStream(
            $info_resolver,
            $resource,
            $stream,
            RevisionStatus::PUBLISHED,
            $keep_original
        );
        $revision = $this->populateRevisionInfo($revision, $info_resolver);

        foreach ($resource->getAllRevisionsIncludingDraft() as $existing_revision) {
            $this->deleteRevision($resource, $existing_revision);
        }

        $resource->addRevision($revision);
        $resource->setStorageID(
            $resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID()
        );

        return $resource;
    }

    public function appendFromRevision(
        StorableResource $resource,
        int $revision_number
    ): StorableResource {
        if ($resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
            throw new \LogicException('You can not replace a draft revision, you must publish it first');
        }
        $existing_revision = $resource->getSpecificRevision($revision_number);
        if ($existing_revision instanceof FileRevision) {
            $info_resolver = new ClonedRevisionInfoResolver(
                $resource->getMaxRevision(false) + 1,
                $existing_revision
            );

            $cloned_revision = $this->revision_repository->blankFromClone(
                $info_resolver,
                $resource,
                $existing_revision
            );

            $this->populateRevisionInfo($cloned_revision, $info_resolver);

            $resource->addRevision($cloned_revision);
            $resource->setStorageID(
                $resource->getStorageID() === '' ? $this->primary_storage_handler->getID() : $resource->getStorageID()
            );
            return $resource;
        }
        return $resource;
    }

    private function buildDraftReplacementRevision(
        StorableResource $resource,
        Revision $new_revision,
        InfoResolver $info_resolver
    ): Revision {
        $current_revision = $resource->getCurrentRevisionIncludingDraft();
        $stream_replacement = new StreamReplacementRevision(
            $resource->getIdentification(),
            $this->extractStream($new_revision)
        );
        $stream_replacement = $this->populateRevisionInfo($stream_replacement, $info_resolver);
        $stream_replacement->setVersionNumber($current_revision->getVersionNumber());
        $stream_replacement->setStatus(RevisionStatus::DRAFT);

        return $stream_replacement;
    }

    /**
     * @description check if a resource exists
     */
    public function has(ResourceIdentification $identification): bool
    {
        return $this->resource_repository->has($identification);
    }

    /**
     * @description after you have modified a resource, you can store it here
     * @throws \ILIAS\ResourceStorage\Policy\FileNamePolicyException
     */
    public function store(StorableResource $resource): void
    {
        foreach ($resource->getAllRevisionsIncludingDraft() as $revision) {
            $this->file_name_policy->check($revision->getInformation()->getSuffix());
        }

        $r = $this->lock_handler->lockTables(
            array_merge(
                $this->resource_repository->getNamesForLocking(),
                $this->revision_repository->getNamesForLocking(),
                $this->information_repository->getNamesForLocking(),
                $this->stakeholder_repository->getNamesForLocking()
            ),
            function () use ($resource): void {
                $this->resource_repository->store($resource);

                foreach ($resource->getAllRevisionsIncludingDraft() as $revision) {
                    $this->storeRevision($revision);
                }

                foreach ($resource->getStakeholders() as $stakeholder) {
                    $this->stakeholder_repository->register($resource->getIdentification(), $stakeholder);
                }
            }
        );

        $r->runAndUnlock();
    }

    /**
     * @description Clone anexisting resource with all it's revisions, stakeholders and information
     */
    public function clone(StorableResource $resource): StorableResource
    {
        $new_resource = $this->newBlank();
        foreach ($resource->getStakeholders() as $stakeholder) {
            $stakeholder = clone $stakeholder;
            $new_resource->addStakeholder($stakeholder);
        }

        foreach ($resource->getAllRevisionsIncludingDraft() as $existing_revision) {
            if (!$existing_revision instanceof FileRevision) {
                continue;
            }
            $info_resolver = new ClonedRevisionInfoResolver(
                $existing_revision->getVersionNumber(),
                $existing_revision
            );

            $existing_revision = $this->stream_access->populateRevision($existing_revision);

            $cloned_revision = new FileStreamRevision(
                $new_resource->getIdentification(),
                $existing_revision->maybeStreamResolver()->getStream(),
                true
            );

            $this->populateRevisionInfo($cloned_revision, $info_resolver);
            $cloned_revision->setVersionNumber($existing_revision->getVersionNumber());

            $new_resource->addRevision($cloned_revision);
        }
        $this->store($new_resource);
        return $new_resource;
    }

    /**
     * @description  Store one Revision
     * @throws \ILIAS\ResourceStorage\Policy\FileNamePolicyException
     */
    public function storeRevision(Revision $revision): void
    {
        $storage_handler = empty($revision->getStorageID())
            ? $this->primary_storage_handler
            : $this->storage_handler_factory->getHandlerForRevision($revision);

        if ($revision instanceof UploadedFileRevision) {
            // check policies
            $this->file_name_policy->check($revision->getInformation()->getSuffix());
            $storage_handler->storeUpload($revision);
        }
        if ($revision instanceof FileStreamRevision) {
            $storage_handler->storeStream($revision);
        }
        if ($revision instanceof CloneRevision) {
            $storage_handler->cloneRevision($revision);
        }
        if ($revision instanceof StreamReplacementRevision) {
            $storage_handler->streamReplacement($revision);
        }
        $this->revision_repository->store($revision);
        $this->information_repository->store($revision->getInformation(), $revision);
    }

    /**
     * @throws ResourceNotFoundException
     * @description Get a Resource out of a Identification
     */
    public function get(ResourceIdentification $identification): StorableResource
    {
        if (isset($this->resource_cache[$identification->serialize()])) {
            return $this->resource_cache[$identification->serialize()];
        }
        $resource = $this->resource_repository->get($identification);

        if ($this->auto_migrate && $resource->getStorageID() !== $this->primary_storage_handler->getID()) {
            global $DIC;
            /** @var Migrator $migrator */
            $migrator = $DIC[\InitResourceStorage::D_MIGRATOR];
            $migrator->migrate($resource, $this->primary_storage_handler->getID());
            $resource->setStorageID($this->primary_storage_handler->getID());
        }

        $this->resource_cache[$identification->serialize()] = $this->populateNakedResourceWithRevisionsAndStakeholders(
            $resource
        );

        return $this->resource_cache[$identification->serialize()];
    }

    public function publish(StorableResource $resource): bool
    {
        $revision = $resource->getCurrentRevisionIncludingDraft();
        if ($revision->getStatus() === RevisionStatus::PUBLISHED) {
            return false;
        }
        $revision->setStatus(RevisionStatus::PUBLISHED);
        $this->store($resource);
        return true;
    }

    public function unpublish(StorableResource $resource): bool
    {
        $revision = $resource->getCurrentRevisionIncludingDraft();
        if ($revision->getStatus() === RevisionStatus::DRAFT) {
            return false;
        }
        $revision->setStatus(RevisionStatus::DRAFT);
        $this->store($resource);
        return true;
    }

    public function extractStream(Revision $revision): FileStream
    {
        switch (true) {
            case $revision instanceof FileStreamRevision:
                return $revision->getStream();
            case $revision instanceof UploadedFileRevision:
                return Streams::ofResource(fopen($revision->getUpload()->getPath(), 'rb'));
            case $revision instanceof CloneRevision:
                return $revision->getRevisionToClone()->getStream();
            case $revision instanceof FileRevision:
                if ($revision->getStorageID() !== '') {
                    return $this->storage_handler_factory->getHandlerForRevision(
                        $revision
                    )->getStream($revision);
                }

                return $this->storage_handler_factory->getHandlerForResource(
                    $this->get($revision->getIdentification())
                )->getStream($revision);
            default:
                throw new \LogicException('This revision type is not supported');
        }
    }

    /**
     * @description Remove a complete revision. if there are other Stakeholder, only your stakeholder gets removed
     * @param ResourceStakeholder|null $stakeholder
     * @return bool whether ResourceStakeholders handled this successful
     */
    public function remove(StorableResource $resource, ResourceStakeholder $stakeholder = null): bool
    {
        $sucessful = true;
        if ($stakeholder instanceof ResourceStakeholder) {
            $this->stakeholder_repository->deregister($resource->getIdentification(), $stakeholder);
            $sucessful = $stakeholder->resourceHasBeenDeleted($resource->getIdentification());
            $resource->removeStakeholder($stakeholder);
            if ($resource->getStakeholders() !== []) {
                return $sucessful;
            }
        }
        foreach ($resource->getStakeholders() as $s) {
            $sucessful = $s->resourceHasBeenDeleted($resource->getIdentification()) && $sucessful;
        }

        foreach ($resource->getAllRevisionsIncludingDraft() as $revision) {
            $this->deleteRevision($resource, $revision);
        }

        $this->storage_handler_factory->getHandlerForResource($resource)->deleteResource($resource);
        $this->resource_repository->delete($resource);

        return $sucessful;
    }

    public function removeRevision(StorableResource $resource, int $revision_number): void
    {
        $reveision_to_delete = $resource->getSpecificRevision($revision_number);
        if ($reveision_to_delete !== null) {
            $this->deleteRevision($resource, $reveision_to_delete);
        }
        $this->store($resource);
    }

    // Container Actions
    public function createDirectoryInsideContainer(
        StorableContainerResource $container,
        string $path_inside_container,
    ): bool {
        $revision = $container->getCurrentRevisionIncludingDraft();
        $stream = $this->extractStream($revision);

        // create directory inside ZipArchive
        try {
            $zip = new \ZipArchive();
            $zip->open($stream->getMetadata()['uri']);
            $path_inside_container = $this->ensurePathInZIP($zip, $path_inside_container, false);
            $zip->close();

            // cleanup revision and flavours
            $this->storage_handler_factory->getHandlerForRevision($revision)->clearFlavours($revision);
            $revision->getInformation()->setSize(filesize($stream->getMetadata()['uri']));
            $this->storeRevision($revision);

            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function ensurePathInZIP(\ZipArchive $zip, string $path, bool $is_file): string
    {
        if ($path === '' || $path === '/') {
            return $path;
        }

        $filename = '';
        if ($is_file) {
            $filename = basename($path);
            $path = dirname($path);
        }

        // try to determine if a path inside the zip exists with or without a slash at the beginning
        // determine root directory of the path using regex
        $parts = explode('/', $path);
        $root = array_shift($parts);
        $root = $root === '' ? array_shift($parts) : $root;

        // check if the root directory exists without a slash at the beginning
        if ($zip->locateName($root . '/') !== false) {
            $root = $root;
        } elseif ($zip->locateName('/' . $root . '/') !== false) {
            // check if the root directory exists with a slash at the beginning
            $root = '/' . $root;
        } else {
            // if the root directory does not exist, create it
            $zip->addEmptyDir($root);
        }

        $path_inside_container = $root;
        foreach ($parts as $part) {
            $path_inside_container .= '/' . $part;
            if ($zip->locateName($path_inside_container . '/') === false) {
                $zip->addEmptyDir($path_inside_container . '/');
            }
        }

        return rtrim($path_inside_container, '/') . '/' . $filename;
    }

    public function removePathInsideContainer(
        StorableContainerResource $container,
        string $path_inside_container,
    ): bool {
        $revision = $container->getCurrentRevisionIncludingDraft();
        $stream = $this->extractStream($revision);

        // create directory inside ZipArchive
        try {
            $zip = new \ZipArchive();
            $zip->open($stream->getMetadata()['uri']);

            $return = $zip->deleteName($path_inside_container);
            // remove all files inside the directory
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $path = $zip->getNameIndex($i);
                if ($path === false) {
                    continue;
                }
                if (strpos($path, $path_inside_container) === 0) {
                    $zip->deleteIndex($i);
                }
            }

            $zip->close();

            // cleanup revision and flavours
            $this->storage_handler_factory->getHandlerForRevision($revision)->clearFlavours($revision);
            $revision->getInformation()->setSize(filesize($stream->getMetadata()['uri']));
            $this->storeRevision($revision);

            return $return;
        } catch (\Throwable $exception) {
            $this->storage_handler_factory->getHandlerForRevision($revision)->clearFlavours($revision);
            return false;
        }
    }

    public function addUploadToContainer(
        StorableContainerResource $container,
        UploadResult $result,
        string $parent_path_inside_container,
    ): bool {
        $revision = $container->getCurrentRevisionIncludingDraft();
        $stream = $this->extractStream($revision);

        // create directory inside ZipArchive
        try {
            $zip = new \ZipArchive();
            $zip->open($stream->getMetadata()['uri']);

            $parent_path_inside_container = $this->ensurePathInZIP($zip, $parent_path_inside_container, false);

            $path_inside_zip = rtrim($parent_path_inside_container, '/') . '/' . $result->getName();

            $return = $zip->addFile(
                $result->getPath(),
                $path_inside_zip
            );
            $zip->close();

            // cleanup revision and flavours
            $this->storage_handler_factory->getHandlerForRevision($revision)->clearFlavours($revision);
            $revision->getInformation()->setSize(filesize($stream->getMetadata()['uri']));
            $this->storeRevision($revision);

            return $return;
        } catch (\Throwable $exception) {
            return false;
        }

        return true;
    }

    public function addStreamToContainer(
        StorableContainerResource $container,
        FileStream $stream,
        string $path_inside_container,
    ): bool {
        $revision = $container->getCurrentRevisionIncludingDraft();
        $revision_stream = $this->extractStream($revision);

        try {
            $zip = new \ZipArchive();
            $zip->open($revision_stream->getMetadata()['uri']);

            $path_inside_container = $this->ensurePathInZIP($zip, $path_inside_container, true);

            $return = $zip->addFromString(
                $path_inside_container,
                (string) $stream
            );
            $zip->close();

            // cleanup revision and flavours
            $this->storage_handler_factory->getHandlerForRevision($revision)->clearFlavours($revision);
            $revision->getInformation()->setSize(filesize($revision_stream->getMetadata()['uri']));
            $this->storeRevision($revision);

            return $return;
        } catch (\Throwable $exception) {
            return false;
        }

        return true;
    }

    private function deleteRevision(StorableResource $resource, Revision $revision): void
    {
        try {
            $this->storage_handler_factory->getHandlerForResource($resource)->deleteRevision($revision);
        } catch (\Throwable $exception) {
        }

        $this->information_repository->delete($revision->getInformation(), $revision);
        $this->revision_repository->delete($revision);
        $resource->removeRevision($revision);
    }

    private function populateNakedResourceWithRevisionsAndStakeholders(StorableResource $resource): StorableResource
    {
        $revisions = $this->revision_repository->get($resource);
        $resource->setRevisions($revisions);

        foreach ($revisions->getAll(true) as $i => $revision) {
            $information = $this->information_repository->get($revision);
            $revision->setInformation($information);
            $revision->setStorageID($resource->getStorageID());
            // $revisions->replaceSingleRevision($this->stream_access->populateRevision($revision)); // currently we do not need populating the stream every time, we will do that in consumers only
        }

        foreach ($this->stakeholder_repository->getStakeholders($resource->getIdentification()) as $s) {
            $resource->addStakeholder($s);
        }

        return $resource;
    }

    private function populateRevisionInfo(Revision $revision, InfoResolver $info_resolver): Revision
    {
        $info = $revision->getInformation();

        $info->setTitle($this->secure($info_resolver->getFileName()));
        $info->setMimeType($info_resolver->getMimeType());
        $info->setSuffix($this->secure($info_resolver->getSuffix()));
        $info->setSize($info_resolver->getSize());
        $info->setCreationDate($info_resolver->getCreationDate());

        $revision->setInformation($info);
        $revision->setTitle($this->secure($info_resolver->getRevisionTitle()));
        $revision->setOwnerId($info_resolver->getOwnerId());

        return $revision;
    }
}
