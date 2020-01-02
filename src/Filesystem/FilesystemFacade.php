<?php
declare(strict_types=1);

namespace ILIAS\Filesystem;

use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Provider\DirectoryAccess;
use ILIAS\Filesystem\Provider\FileAccess;
use ILIAS\Filesystem\Provider\FileStreamAccess;
use ILIAS\Filesystem\Stream\FileStream;

/**
 * Class FilesystemFacade
 *
 * The filesystem facade is used internally to satisfy the Filesystem interface because the implementations are split into
 * different classes to reduce the size and responsibility of each class.
 *
 * This class simply delegates the work to the classes which are responsible for the task.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 *
 * @internal
 */
final class FilesystemFacade implements Filesystem
{

    /**
     * @var FileStreamAccess $fileStreamAccess
     */
    private $fileStreamAccess;
    /**
     * @var FileAccess $fileAccess
     */
    private $fileAccess;
    /**
     * @var DirectoryAccess $directoryAccess
     */
    private $directoryAccess;


    /**
     * FilesystemFacade constructor.
     *
     * Creates a new instance of the facade with the provided access classes.
     *
     * @param FileStreamAccess $fileStreamAccess
     * @param FileAccess       $fileAccess
     * @param DirectoryAccess  $directoryAccess
     */
    public function __construct(FileStreamAccess $fileStreamAccess, FileAccess $fileAccess, DirectoryAccess $directoryAccess)
    {
        $this->fileStreamAccess = $fileStreamAccess;
        $this->fileAccess = $fileAccess;
        $this->directoryAccess = $directoryAccess;
    }


    /**
     * @inheritDoc
     */
    public function hasDir(string $path) : bool
    {
        return $this->directoryAccess->hasDir($path);
    }


    /**
     * @inheritDoc
     */
    public function listContents(string $path = '', bool $recursive = false) : array
    {
        return $this->directoryAccess->listContents($path, $recursive);
    }


    /**
     * @inheritDoc
     */
    public function createDir(string $path, string $visibility = Visibility::PUBLIC_ACCESS)
    {
        $this->directoryAccess->createDir($path, $visibility);
    }


    /**
     * @inheritDoc
     */
    public function copyDir(string $source, string $destination)
    {
        $this->directoryAccess->copyDir($source, $destination);
    }


    /**
     * @inheritDoc
     */
    public function deleteDir(string $path)
    {
        $this->directoryAccess->deleteDir($path);
    }


    /**
     * @inheritDoc
     */
    public function read(string $path) : string
    {
        return $this->fileAccess->read($path);
    }


    /**
     * @inheritDoc
     */
    public function has(string $path) : bool
    {
        return $this->fileAccess->has($path);
    }


    /**
     * @inheritDoc
     */
    public function getMimeType(string $path) : string
    {
        return $this->fileAccess->getMimeType($path);
    }


    /**
     * @inheritDoc
     */
    public function getTimestamp(string $path) : \DateTimeImmutable
    {
        return $this->fileAccess->getTimestamp($path);
    }


    /**
     * @inheritDoc
     */
    public function getSize(string $path, int $fileSizeUnit) : DataSize
    {
        return $this->fileAccess->getSize($path, $fileSizeUnit);
    }


    /**
     * @inheritDoc
     */
    public function setVisibility(string $path, string $visibility) : bool
    {
        return $this->fileAccess->setVisibility($path, $visibility);
    }


    /**
     * @inheritDoc
     */
    public function getVisibility(string $path) : string
    {
        return $this->fileAccess->getVisibility($path);
    }


    /**
     * @inheritDoc
     */
    public function readStream(string $path) : FileStream
    {
        return $this->fileStreamAccess->readStream($path);
    }


    /**
     * @inheritDoc
     */
    public function writeStream(string $path, FileStream $stream)
    {
        $this->fileStreamAccess->writeStream($path, $stream);
    }


    /**
     * @inheritDoc
     */
    public function putStream(string $path, FileStream $stream)
    {
        $this->fileStreamAccess->putStream($path, $stream);
    }


    /**
     * @inheritDoc
     */
    public function updateStream(string $path, FileStream $stream)
    {
        $this->fileStreamAccess->updateStream($path, $stream);
    }


    /**
     * @inheritDoc
     */
    public function write(string $path, string $content)
    {
        $this->fileAccess->write($path, $content);
    }


    /**
     * @inheritDoc
     */
    public function update(string $path, string $newContent)
    {
        $this->fileAccess->update($path, $newContent);
    }


    /**
     * @inheritDoc
     */
    public function put(string $path, string $content)
    {
        $this->fileAccess->put($path, $content);
    }


    /**
     * @inheritDoc
     */
    public function delete(string $path)
    {
        $this->fileAccess->delete($path);
    }


    /**
     * @inheritDoc
     */
    public function readAndDelete(string $path) : string
    {
        return $this->fileAccess->readAndDelete($path);
    }


    /**
     * @inheritDoc
     */
    public function rename(string $path, string $newPath)
    {
        $this->fileAccess->rename($path, $newPath);
    }


    /**
     * @inheritDoc
     */
    public function copy(string $path, string $copyPath)
    {
        $this->fileAccess->copy($path, $copyPath);
    }
}
