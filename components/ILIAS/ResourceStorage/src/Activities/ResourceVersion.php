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

use ILIAS\ResourceStorage\Revision\Revision;

/**
 * One revision of a resource, as an activity talks about it.
 */
readonly class ResourceVersion
{
    public function __construct(
        public int $version,
        public string $title,
        public string $mime_type,
        public int $size,
        public int $owner_id,
        public string $created,
    ) {
    }

    public static function of(Revision $revision): self
    {
        $information = $revision->getInformation();

        return new self(
            $revision->getVersionNumber(),
            $information->getTitle(),
            $information->getMimeType(),
            $information->getSize(),
            $revision->getOwnerId(),
            $information->getCreationDate()->format(\DateTimeInterface::ATOM),
        );
    }
}
