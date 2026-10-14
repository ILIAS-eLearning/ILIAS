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

namespace ILIAS\ResourceStorage\Activities;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Information;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\RevisionStatus;
use ILIAS\ResourceStorage\IRSSServices;

/**
 * Hands the ResourceStorage to the activities: the few reads they need, in a
 * shape that can be mocked.
 */
class ServiceProvider
{
    public function __construct(
        private readonly IRSSServices $services,
    ) {
    }

    public function get(): IRSSServices
    {
        return $this->services;
    }

    public function find(string $rid): ?ResourceIdentification
    {
        return $this->get()->manage()->find($rid);
    }

    /**
     * The user who created the current revision - the closest thing to an owner
     * a resource has.
     */
    public function getOwnerId(ResourceIdentification $rid): int
    {
        return $this->get()->manage()->getResource($rid)->getCurrentRevision()->getOwnerId();
    }

    public function getInformation(ResourceIdentification $rid): Information
    {
        return $this->get()->manage()->getResource($rid)->getCurrentRevision()->getInformation();
    }

    /**
     * Reading the bytes of a resource goes through a consumer, always.
     */
    public function readStream(ResourceIdentification $rid, ?int $version = null): FileStream
    {
        $consumer = $this->get()->consume()->stream($rid);

        if ($version !== null) {
            $consumer = $consumer->setRevisionNumber($version);
        }

        return $consumer->getStream();
    }

    /**
     * @return list<Revision> oldest first
     */
    public function getRevisions(ResourceIdentification $rid): array
    {
        $revisions = $this->get()->manage()->getResource($rid)->getAllRevisions();

        usort($revisions, static fn(Revision $a, Revision $b): int => $a->getVersionNumber() <=> $b->getVersionNumber());

        return $revisions;
    }

    /**
     * A published revision only: a draft is work in progress of whoever edits
     * the resource, and not part of its history yet.
     */
    public function getRevision(ResourceIdentification $rid, int $version): ?Revision
    {
        $revision = $this->get()->manage()->getResource($rid)->getSpecificRevision($version);

        return $revision?->getStatus() === RevisionStatus::PUBLISHED ? $revision : null;
    }
}
