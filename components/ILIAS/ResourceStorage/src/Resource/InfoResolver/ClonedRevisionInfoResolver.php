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

namespace ILIAS\ResourceStorage\Resource\InfoResolver;

use DateTimeImmutable;
use ILIAS\ResourceStorage\Revision\FileRevision;

/**
 * Class ClonedRevisionInfoResolver
 * @package ILIAS\ResourceStorage\Resource\InfoResolver
 * @internal
 */
class ClonedRevisionInfoResolver implements InfoResolver
{
    protected \ILIAS\ResourceStorage\Information\Information $info;
    protected int $next_version_number;
    protected FileRevision $existing_revision;

    /**
     * ClonedRevisionInfoResolver constructor.
     */
    public function __construct(int $next_version_number, FileRevision $existing_revision)
    {
        $this->next_version_number = $next_version_number;
        $this->existing_revision = $existing_revision;
        $this->info = $existing_revision->getInformation();
    }

    public function getNextVersionNumber(): int
    {
        return $this->next_version_number;
    }

    public function getOwnerId(): int
    {
        return $this->existing_revision->getOwnerId();
    }

    public function getRevisionTitle(): string
    {
        return $this->existing_revision->getTitle();
    }

    public function getFileName(): string
    {
        return $this->info->getTitle();
    }

    public function getMimeType(): string
    {
        return $this->info->getMimeType();
    }

    public function getSuffix(): string
    {
        return $this->info->getSuffix();
    }

    public function getCreationDate(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function getSize(): int
    {
        return $this->info->getSize();
    }
}
