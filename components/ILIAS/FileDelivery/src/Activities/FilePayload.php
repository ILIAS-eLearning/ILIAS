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

namespace ILIAS\FileDelivery\Activities;

/**
 * A file on its way out of the system: everything a consumer needs to write it
 * somewhere, with the content base64 encoded so it survives any transport.
 *
 * Every activity that delivers a file returns this, no matter where the file
 * came from. Consumers can therefore treat a temporary file and a resource of
 * the ResourceStorage exactly alike.
 */
readonly class FilePayload
{
    public function __construct(
        public string $filename,
        public string $mime_type,
        public int $size,
        public string $content,
    ) {
    }
}
