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

use ILIAS\FileDelivery\Activities\FilePayload;

/**
 * The zip archive of a container: a file payload like every other delivery, so
 * a consumer writes it the same way, plus the files that could not be packed.
 */
readonly class ContainerArchive
{
    /**
     * @param list<SkippedFile> $skipped
     */
    public function __construct(
        public string $filename,
        public string $mime_type,
        public int $size,
        public string $content,
        public array $skipped,
    ) {
    }

    /**
     * @param list<SkippedFile> $skipped
     */
    public static function of(FilePayload $payload, array $skipped): self
    {
        return new self(
            $payload->filename,
            $payload->mime_type,
            $payload->size,
            $payload->content,
            $skipped,
        );
    }
}
