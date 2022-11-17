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

namespace ILIAS\ResourceStorage\Consumer\StreamAccess;

use ILIAS\Filesystem\Stream\Stream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class TokenStream extends Stream
{
    private ?string $mime_type = null;

    public function getMimeType(): ?string
    {
        if ($this->mime_type === null) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            //We only need the first few bytes to determine the mime-type this helps to reduce RAM-Usage
            $this->rewind();
            $this->mime_type = finfo_buffer($finfo, $this->read(255)) ?: 'application/octet-stream';
            $this->rewind();
        }
        return $this->mime_type;
    }
}
