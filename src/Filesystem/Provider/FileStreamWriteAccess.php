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

use ILIAS\Filesystem\Stream\FileStream;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
interface FileStreamWriteAccess
{
    /**
     * Writes the stream to a new file.
     * The directory path to the file will be created.
     *
     * The stream will be closed after the write operation is done. Please note that the
     * resource must be detached from the stream in order to write to the file.
     *
     * @param string     $path   The file which should be used to write the stream into.
     * @param FileStream $stream The stream which should be written into the new file.
     *
     * @see     FileStream::detach()
     */
    public function writeStream(string $path, FileStream $stream): void;

    /**
     * Creates a new file or updates an existing one.
     * If the file is updated its content will be truncated before writing the stream.
     *
     * The stream will be closed after the write operation is done. Please note that the
     * resource must be detached from the stream in order to write to the file.
     *
     * @param string     $path   The file which should be used to write the stream into.
     * @param FileStream $stream The stream which should be written to the file.
     *
     * @see     FileStream::detach()
     */
    public function putStream(string $path, FileStream $stream): void;

    /**
     * Updates an existing file.
     * The file content will be truncated to 0.
     *
     * The stream will be closed after the write operation is done. Please note that the
     * resource must be detached from the stream in order to write to the file.
     *
     * @param string     $path   The path to the file which should be updated.
     * @param FileStream $stream The stream which should be used to update the file content.
     *
     * @see     FileStream::detach()
     */
    public function updateStream(string $path, FileStream $stream): void;
}
