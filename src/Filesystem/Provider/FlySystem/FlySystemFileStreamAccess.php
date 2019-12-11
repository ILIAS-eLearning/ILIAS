<?php
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

/**
 * Class FlySystemFileStreamAccess
 *
 * Streaming access implementation of the fly system library.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
final class FlySystemFileStreamAccess implements FileStreamAccess
{

    /**
     * @var FilesystemInterface $flySystemFS
     */
    private $flySystemFS;

    /**
     * FlySystemFileStreamAccess constructor.
     *
     * @param FilesystemInterface $flySystemFS   A configured fly system filesystem instance.
     */
    public function __construct(FilesystemInterface $flySystemFS)
    {
        $this->flySystemFS = $flySystemFS;
    }

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
     * @since   5.3
     * @version 1.0
     *
     * @see FileStream::close()
     */
    public function readStream(string $path) : FileStream
    {
        try {
            $resource = $this->flySystemFS->readStream($path);
            if ($resource === false) {
                throw new IOException("Could not open stream for file \"$path\"");
            }

            $stream = Streams::ofResource($resource);
            return $stream;
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        }
    }


    /**
     * Writes the stream to a new file.
     * The directory path to the file will be created.
     *
     * The stream will be closed after the write operation is done. Please note that the
     * resource must be detached from the stream in order to write to the file.
     *
     * @param string                    $path   The file which should be used to write the stream into.
     * @param FileStream                $stream The stream which should be written into the new file.
     *
     * @return void
     * @throws FileAlreadyExistsException If the file already exists.
     * @throws IOException If the file could not be written to the filesystem.
     * @since   5.3
     * @version 1.0
     *
     * @see     FileStream::detach()
     */
    public function writeStream(string $path, FileStream $stream)
    {
        $resource = $stream->detach();
        try {
            if (!is_resource($resource)) {
                throw new \InvalidArgumentException('The given stream must not be detached.');
            }

            $result = $this->flySystemFS->writeStream($path, $resource);

            if ($result === false) {
                throw new IOException("Could not write stream to file \"$path\"");
            }
        } catch (FileExistsException $ex) {
            throw new FileAlreadyExistsException("File \"$path\" already exists.", 0, $ex);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }


    /**
     * Creates a new file or updates an existing one.
     * If the file is updated its content will be truncated before writing the stream.
     *
     * The stream will be closed after the write operation is done. Please note that the
     * resource must be detached from the stream in order to write to the file.
     *
     * @param string                     $path   The file which should be used to write the stream into.
     * @param FileStream                 $stream The stream which should be written to the file.
     *
     * @return void
     * @throws IOException If the stream could not be written to the file.
     * @since   5.3
     * @version 1.0
     *
     * @see     FileStream::detach()
     */
    public function putStream(string $path, FileStream $stream)
    {
        $resource = $stream->detach();
        try {
            if (!is_resource($resource)) {
                throw new \InvalidArgumentException('The given stream must not be detached.');
            }

            $result = $this->flySystemFS->putStream($path, $resource);

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
     *
     * @param string                    $path   The path to the file which should be updated.
     * @param FileStream                $stream The stream which should be used to update the file content.
     *
     * @return void
     * @throws FileNotFoundException If the file which should be updated doesn't exist.
     * @throws IOException If the file could not be updated.
     * @since   5.3
     * @version 1.0
     */
    public function updateStream(string $path, FileStream $stream)
    {
        $resource = $stream->detach();
        try {
            if (!is_resource($resource)) {
                throw new \InvalidArgumentException('The given stream must not be detached.');
            }

            $result = $this->flySystemFS->updateStream($path, $resource);

            if ($result === false) {
                throw new IOException("Could not update file \"$path\"");
            }
        } catch (\League\Flysystem\FileNotFoundException $ex) {
            throw new FileNotFoundException("File \"$path\" not found.", 0, $ex);
        } finally {
            if (is_resource($resource)) {
                fclose($resource);
            }
        }
    }
}
