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

namespace ILIAS\Filesystem\Util;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait MemoryStreamToTempFileStream
{
    protected function maybeSafeToTempStream(FileStream $stream): FileStream
    {
        if ($stream->getMetadata()['uri'] === 'php://memory') {
            // save stream to temp file
            $tmp = tmpfile();
            fwrite($tmp, (string)$stream);
            $temp_stream = Streams::ofResource($tmp);
            $temp_stream->rewind();

            return $temp_stream;
        }
        return $stream;
    }
}
