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

namespace ILIAS\FileUpload\Activities;

use ILIAS\Filesystem\Stream\FileStream;

/**
 * The file an activity was given, ready to be worked with.
 *
 * The bytes arrived inside the call that uses them - there is no upload step of
 * its own - but they have to live in a file while the activity runs: a stream
 * over a string has no path, and both the ResourceStorage and ilObjFile take
 * the name of what they store from the file behind the stream.
 *
 * So the content sits in the temp storage for the duration of the request. It
 * belongs to the activity that read it, and that activity releases it again -
 * nothing addressable is ever handed out, and nothing is left behind.
 */
readonly class FileContent
{
    public function __construct(
        private TempFileStore $files,
        private string $handle,
        public string $filename,
        public string $mime_type,
        public int $size,
    ) {
    }

    public function stream(): FileStream
    {
        return $this->files->readStream($this->handle);
    }

    public function release(): void
    {
        if ($this->files->has($this->handle)) {
            $this->files->delete($this->handle);
        }
    }
}
