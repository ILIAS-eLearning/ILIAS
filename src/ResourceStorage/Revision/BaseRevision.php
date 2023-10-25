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

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Consumer\StreamAccess\Token;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamAccess;
use ILIAS\ResourceStorage\Consumer\StreamAccess\StreamResolver;

/**
 * Class NullRevision
 * @author Fabian Schmid <fabian@sr.solutions.ch>
 */
abstract class BaseRevision implements Revision
{
    private ?string $storage_id = null;
    private ?StreamResolver $stream_resolver = null;
    private ResourceIdentification $identification;

    protected RevisionStatus $status = RevisionStatus::PUBLISHED;

    /**
     * NullRevision constructor.
     */
    public function __construct(ResourceIdentification $identification)
    {
        $this->identification = $identification;
    }

    /**
     * @inheritDoc
     */
    public function getIdentification(): ResourceIdentification
    {
        return $this->identification;
    }

    public function setStorageID(string $storage_id): void
    {
        $this->storage_id = $storage_id;
    }

    public function getStorageID(): string
    {
        return $this->storage_id ?? '';
    }


    public function withStreamResolver(?StreamResolver $stream_resolver = null): Revision
    {
        $clone = clone $this;
        $clone->stream_resolver = $stream_resolver;
        return $clone;
    }

    public function maybeStreamResolver(): ?StreamResolver
    {
        return $this->stream_resolver;
    }

    public function getStatus(): RevisionStatus
    {
        return $this->status;
    }

    public function setStatus(RevisionStatus $status): void
    {
        $this->status = $status;
    }
}
