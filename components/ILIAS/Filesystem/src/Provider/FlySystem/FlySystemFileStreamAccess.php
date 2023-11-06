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

namespace ILIAS\Filesystem\Provider\FlySystem;

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Provider\FileStreamAccess;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;
use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToReadFile;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
final class FlySystemFileStreamAccess implements FileStreamAccess
{
    public function __construct(
        private FilesystemOperator $flysystem_operator
    ) {
    }

    /**
     * Opens a readable stream of the file.
     * Please make sure to close the stream after the work is done with Stream::close()
     * @see FileStream::close()
     */
    public function readStream(string $path): FileStream
    {
        try {
            $resource = $this->flysystem_operator->readStream($path);
            if ($resource === false) {
                throw new IOException("Could not open stream for file \"$path\"");
            }
            return Streams::ofResource($resource);
        } catch (UnableToRetrieveMetadata|UnableToReadFile $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        }
    }

    /**
     * Writes the stream to a new file.
     * The directory path to the file will be created.
     *
     * The stream will be closed after the write operation is done. Please note that the
     * resource must be detached from the stream in order to write to the file.
     * @see     FileStream::detach()
     */
    public function writeStream(string $path, FileStream $stream): void
    {
        $resource = $stream->detach();
        if (!is_resource($resource)) {
            throw new \InvalidArgumentException('The given stream must not be detached.');
        }
        if ($this->flysystem_operator->fileExists($path)) {
            throw new FileAlreadyExistsException("File \"$path\" already exists.");
        }
        try {
            $this->flysystem_operator->writeStream($path, $resource);
        } catch (UnableToWriteFile $ex) {
            throw new IOException("Could not write stream to file \"$path\"", 0, $ex);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    /**
     * Creates a new file or updates an existing one.
     * If the file is updated its content will be truncated before writing the stream.
     * @see     FileStream::detach()
     */
    public function putStream(string $path, FileStream $stream): void
    {
        $resource = $stream->detach();
        try {
            if (!is_resource($resource)) {
                throw new \InvalidArgumentException('The given stream must not be detached.');
            }

            $result = $this->flysystem_operator->putStream($path, $resource);

            if ($result === false) {
                throw new IOException("Could not put stream content into \"$path\"");
            }
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }

    /**
     * Updates an existing file.
     * The file content will be truncated to 0.
     *
     * The stream will be closed after the write operation is done. Please note that the
     * resource must be detached from the stream in order to write to the file.
     */
    public function updateStream(string $path, FileStream $stream): void
    {
        $resource = $stream->detach();
        try {
            if (!is_resource($resource)) {
                throw new \InvalidArgumentException('The given stream must not be detached.');
            }
            // FlySystem 3 has no updateStream method, so we have to use writeStream instead.
            $this->flysystem_operator->writeStream($path, $resource);
        } catch (UnableToWriteFile $ex) {
            throw new FileNotFoundException("Unable to update Stream in \"$path\".", 0, $ex);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }
}
