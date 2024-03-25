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

namespace ILIAS\ResourceStorage\Manager;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Collection\CollectionBuilder;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Preloader\RepositoryPreloader;
use ILIAS\ResourceStorage\Resource\InfoResolver\StreamInfoResolver;
use ILIAS\ResourceStorage\Resource\InfoResolver\UploadInfoResolver;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Resource\ResourceType;
use ILIAS\ResourceStorage\Revision\RevisionStatus;

/**
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
abstract class BaseManager
{
    protected ResourceBuilder $resource_builder;
    protected CollectionBuilder $collection_builder;
    protected RepositoryPreloader $preloader;

    /**
     * Manager constructor.
     */
    public function __construct(
        ResourceBuilder $resource_builder,
        CollectionBuilder $collection_builder,
        RepositoryPreloader $preloader
    ) {
        $this->resource_builder = $resource_builder;
        $this->collection_builder = $collection_builder;
        $this->preloader = $preloader;
    }

    /**
     * @param bool|string $mimetype
     * @return void
     */
    protected function checkZIP(bool|string $mimetype): void
    {
        if (!in_array($mimetype, ['application/zip', 'application/x-zip-compressed'])) {
            throw new \LogicException("Cant create container resource since stream is not a ZIP");
        }
    }

    /**
     * @description Publish a resource. A resource can contain a maximum of one revision in DRAFT on top status.
     * This method can be used to publish this revision. If the latest revision is already published, nothing changes.
     */
    public function publish(ResourceIdentification $rid): void
    {
        $this->resource_builder->publish($this->resource_builder->get($rid));
    }

    /**
     * @description Unpublish a resource. The newest revision of a resource is set to the DRAFT status.
     * If the latest revision is already in DRAFT, nothing changes.
     */
    public function unpublish(ResourceIdentification $rid): void
    {
        $this->resource_builder->unpublish($this->resource_builder->get($rid));
    }

    protected function newStreamBased(
        FileStream $stream,
        ResourceStakeholder $stakeholder,
        ResourceType $type,
        string $revision_title = null
    ): ResourceIdentification {
        $info_resolver = new StreamInfoResolver(
            $stream,
            1,
            $stakeholder->getOwnerOfNewResources(),
            $revision_title ?? $stream->getMetadata()['uri']
        );

        $resource = $this->resource_builder->newFromStream(
            $stream,
            $info_resolver,
            true,
            $type
        );
        $resource->addStakeholder($stakeholder);
        $this->resource_builder->store($resource);

        return $resource->getIdentification();
    }

    public function find(string $identification): ?ResourceIdentification
    {
        $resource_identification = new ResourceIdentification($identification);

        if ($this->resource_builder->has($resource_identification)) {
            return $resource_identification;
        }

        return null;
    }

    // Resources

    public function getResource(ResourceIdentification $i): StorableResource
    {
        $this->preloader->preload([$i->serialize()]);
        return $this->resource_builder->get($i);
    }

    public function remove(ResourceIdentification $identification, ResourceStakeholder $stakeholder): void
    {
        $this->resource_builder->remove($this->resource_builder->get($identification), $stakeholder);
        if (!$this->resource_builder->has($identification)) {
            $this->collection_builder->notififyResourceDeletion($identification);
        }
    }

    public function clone(ResourceIdentification $identification): ResourceIdentification
    {
        $resource = $this->resource_builder->clone($this->resource_builder->get($identification));

        return $resource->getIdentification();
    }

    // Revision

    /**
     * @description  Append a new revision from an UploadResult. By passing $draft = true, the revision will be created as a
     *               DRAFT on top of the current revision. Consumers will always use the latest published revision.
     *               Appending new Revisions is not possible if the latest revision is already a DRAFT. In this case,
     *               the DRAFT will be updated.
     */
    public function appendNewRevision(
        ResourceIdentification $identification,
        UploadResult $result,
        ResourceStakeholder $stakeholder,
        string $revision_title = null,
        bool $draft = false
    ): Revision {
        if ($result->isOK()) {
            if (!$this->resource_builder->has($identification)) {
                throw new \LogicException(
                    "Resource not found, can't append new version in: " . $identification->serialize()
                );
            }
            $resource = $this->resource_builder->get($identification);
            if ($resource->getType() === ResourceType::CONTAINER) {
                $this->checkZIP($result->getMimeType());
            }

            $info_resolver = new UploadInfoResolver(
                $result,
                $resource->getMaxRevision(true) + 1,
                $stakeholder->getOwnerOfNewResources(),
                $revision_title ?? $result->getName()
            );

            $this->resource_builder->append(
                $resource,
                $result,
                $info_resolver,
                $draft ? RevisionStatus::DRAFT : RevisionStatus::PUBLISHED
            );
            $resource->addStakeholder($stakeholder);

            $this->resource_builder->store($resource);

            return $resource->getCurrentRevisionIncludingDraft();
        }
        throw new \LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
    }

    /**
     * @throws \ILIAS\ResourceStorage\Policy\FileNamePolicyException if the filename is not allowed
     * @throws \LogicException if the resource is not found
     * @throws \LogicException if the resource is a container and the stream is not a ZIP
     * @throws \LogicException if the latest revision is a DRAFT
     */
    public function replaceWithUpload(
        ResourceIdentification $identification,
        UploadResult $result,
        ResourceStakeholder $stakeholder,
        string $revision_title = null
    ): Revision {
        if ($result->isOK()) {
            if (!$this->resource_builder->has($identification)) {
                throw new \LogicException(
                    "Resource not found, can't append new version in: " . $identification->serialize()
                );
            }
            $resource = $this->resource_builder->get($identification);
            if ($resource->getType() === ResourceType::CONTAINER) {
                $this->checkZIP($result->getMimeType());
            }
            if ($resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
                throw new \LogicException(
                    "Can't replace DRAFT revision, use appendNewRevision instead to update the DRAFT"
                );
            }
            $info_resolver = new UploadInfoResolver(
                $result,
                $resource->getMaxRevision(true) + 1,
                $stakeholder->getOwnerOfNewResources(),
                $revision_title ?? $result->getName()
            );
            $this->resource_builder->replaceWithUpload(
                $resource,
                $result,
                $info_resolver
            );
            $resource->addStakeholder($stakeholder);

            $this->resource_builder->store($resource);

            return $resource->getCurrentRevisionIncludingDraft();
        }
        throw new \LogicException("Can't handle UploadResult: " . $result->getStatus()->getMessage());
    }

    /**
     * @description Append a new revision from a stream. By passing $draft = true, the revision will be created as a
     *              DRAFT on top of the current revision. Consumers will always use the latest published revision.
     *              Appending new Revisions is not possible if the latest revision is already a DRAFT. In this case,
     *              the DRAFT will be updated.
     */
    public function appendNewRevisionFromStream(
        ResourceIdentification $identification,
        FileStream $stream,
        ResourceStakeholder $stakeholder,
        string $revision_title = null,
        bool $draft = false
    ): Revision {
        if (!$this->resource_builder->has($identification)) {
            throw new \LogicException(
                "Resource not found, can't append new version in: " . $identification->serialize()
            );
        }

        $resource = $this->resource_builder->get($identification);
        if ($resource->getType() === ResourceType::CONTAINER) {
            $this->checkZIP(mime_content_type($stream->getMetadata()['uri']));
        }
        $info_resolver = new StreamInfoResolver(
            $stream,
            $resource->getMaxRevision(true) + 1,
            $stakeholder->getOwnerOfNewResources(),
            $revision_title ?? $stream->getMetadata()['uri']
        );

        $this->resource_builder->appendFromStream(
            $resource,
            $stream,
            $info_resolver,
            $draft ? RevisionStatus::DRAFT : RevisionStatus::PUBLISHED,
            true
        );

        $resource->addStakeholder($stakeholder);

        $this->resource_builder->store($resource);

        return $resource->getCurrentRevisionIncludingDraft();
    }

    /**
     * @throws \ILIAS\ResourceStorage\Policy\FileNamePolicyException if the filename is not allowed
     * @throws \LogicException if the resource is not found
     * @throws \LogicException if the resource is a container and the stream is not a ZIP
     * @throws \LogicException if the latest revision is a DRAFT
     */
    public function replaceWithStream(
        ResourceIdentification $identification,
        FileStream $stream,
        ResourceStakeholder $stakeholder,
        string $revision_title = null
    ): Revision {
        if (!$this->resource_builder->has($identification)) {
            throw new \LogicException(
                "Resource not found, can't append new version in: " . $identification->serialize()
            );
        }

        $resource = $this->resource_builder->get($identification);
        if ($resource->getCurrentRevisionIncludingDraft()->getStatus() === RevisionStatus::DRAFT) {
            throw new \LogicException(
                "Can't replace DRAFT revision, use appendNewRevisionFromStream instead to update the DRAFT"
            );
        }
        if ($resource->getType() === ResourceType::CONTAINER) {
            $this->checkZIP(mime_content_type($stream->getMetadata()['uri']));
        }
        $info_resolver = new StreamInfoResolver(
            $stream,
            $resource->getMaxRevision(true) + 1,
            $stakeholder->getOwnerOfNewResources(),
            $revision_title ?? $stream->getMetadata()['uri']
        );

        $this->resource_builder->replaceWithStream(
            $resource,
            $stream,
            $info_resolver,
            true
        );
        $resource->addStakeholder($stakeholder);

        $this->resource_builder->store($resource);

        return $resource->getCurrentRevisionIncludingDraft();
    }

    public function getCurrentRevision(
        ResourceIdentification $identification
    ): Revision {
        return $this->resource_builder->get($identification)->getCurrentRevision();
    }

    public function getCurrentRevisionIncludingDraft(
        ResourceIdentification $identification
    ): Revision {
        return $this->resource_builder->get($identification)->getCurrentRevisionIncludingDraft();
    }

    public function updateRevision(Revision $revision): bool
    {
        $this->resource_builder->storeRevision($revision);

        return true;
    }

    public function rollbackRevision(ResourceIdentification $identification, int $revision_number): bool
    {
        $resource = $this->resource_builder->get($identification);
        $this->resource_builder->appendFromRevision($resource, $revision_number);
        $this->resource_builder->store($resource);

        return true;
    }

    public function removeRevision(ResourceIdentification $identification, int $revision_number): bool
    {
        $resource = $this->resource_builder->get($identification);
        $this->resource_builder->removeRevision($resource, $revision_number);
        $this->resource_builder->store($resource);

        return true;
    }
}
