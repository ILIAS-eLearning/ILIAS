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

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
interface FileStreamReadAccess
{
    /**
     * Opens a readable stream of the file.
     * Please make sure to close the stream after the work is done with Stream::close()
     *
     * @param string $path The path to the file which should be used to open the new stream.
     *
     * @return FileStream The newly created file stream.
     *
     * @throws FileNotFoundException    If the file could not be found.
     * @throws IOException              If the stream could not be opened.
     *
     *
     * @see FileStream::close()
     */
    public function readStream(string $path): FileStream;
}
