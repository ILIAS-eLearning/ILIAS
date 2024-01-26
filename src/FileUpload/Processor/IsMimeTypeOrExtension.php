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

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait IsMimeTypeOrExtension
{
    protected function isMimeTypeOrExtension(
        Metadata $metadata,
        string $extention,
        array $mime_types
    ) : bool {
        // is mime type
        $mime_type = strtolower($metadata->getMimeType());
        $mime_types = array_map('strtolower', $mime_types);
        if (in_array($mime_type, $mime_types, true)) {
            return true;
        }
        // is extension
        if (substr_compare(
            $metadata->getFilename(),
            $extention,
            -strlen($extention),
            null,
            true
        ) === 0) {
            return true;
        }
        return false;
    }
}
