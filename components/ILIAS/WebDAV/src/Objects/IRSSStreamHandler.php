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

namespace ILIAS\WebDAV\Objects;

use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Consumer\Consumers;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\WebDAV\DataCheck;
use ILIAS\ResourceStorage\Revision\RevisionStatus;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class IRSSStreamHandler implements StreamHandler
{
    use DataCheck;

    private Manager $manager;
    private Consumers $consumer;
    private \ilObjFileStakeholder $stakeholder;

    public function __construct(
        private ?ResourceIdentification $resource_identification
    ) {
        global $DIC; // TODO remove Service Locator
        $this->manager = $DIC->resourceStorage()->manage();
        $this->consumer = $DIC->resourceStorage()->consume();
        $this->stakeholder = new \ilObjFileStakeholder();
    }

    public function get(): ?FileStream
    {
        if ($this->resource_identification === null) {
            return null;
        }
        try {
            return $this->consumer->stream($this->resource_identification)->getStream();
        } catch (\Throwable) {
            return null;
        }
    }

    public function put(string $title, mixed $data, bool $publish): bool
    {
        if (is_resource($data)) {
            $stream = Streams::ofResource($data);
        } elseif (is_string($data) && $data !== '') {
            $stream = Streams::ofString($data);
        } else {
            return false;
        }

        // Detect the empty `_Empty` placeholder revision created in
        // TreeProxyRepository::createObject(). If the resource currently holds
        // only that single DRAFT placeholder, remove it after the real
        // revision has been published so the file does not appear with two
        // versions (one empty, one with content).
        $placeholder_revision_number = null;
        $resource = $this->manager->getResource($this->resource_identification);
        if (count($resource->getAllRevisionsIncludingDraft()) === 1) {
            $only = $resource->getCurrentRevisionIncludingDraft();
            if ($only->getStatus() === RevisionStatus::DRAFT) {
                $placeholder_revision_number = $only->getVersionNumber();
            }
        }

        $this->manager->appendNewRevisionFromStream(
            $this->resource_identification,
            $stream,
            $this->stakeholder,
            $title,
            true
        );

        if ($publish) {
            $this->manager->publish($this->resource_identification);
        }

        if ($placeholder_revision_number !== null && $publish) {
            $this->manager->removeRevision(
                $this->resource_identification,
                $placeholder_revision_number
            );
        }

        return true;
    }

    public function publish(): void
    {
        if ($this->manager->getCurrentRevisionIncludingDraft(
            $this->resource_identification
        )->getStatus() === RevisionStatus::DRAFT
        ) {
            $this->manager->publish($this->resource_identification);
        }
    }

}
