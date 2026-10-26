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

namespace ILIAS\File\Activities;

/**
 * One file of a listing.
 *
 * It carries the resource identification, so a client can hand it straight to
 * `ILIAS\ResourceStorage\Activities\DeliverResource` to get the content.
 */
readonly class FileEntry
{
    public function __construct(
        public int $ref_id,
        public int $obj_id,
        public string $title,
        public string $description,
        public string $filename,
        public string $mime_type,
        public int $size,
        public int $version,
        public string $rid,
    ) {
    }
}
