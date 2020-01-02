<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Decorator;

use DateTime;
use ilFileUtils;
use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Visibility;

/**
 * Class ReadOnlyDecorator
 *
 * The filesystem ready only decorator provides read only access and will throw
 * an Exception whenever code tries to write files.
 *
 * @package ILIAS\Filesystem\Decorator
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 * @since   5.3.6
 */
final class ReadOnlyDecorator implements Filesystem
{

    /**
     * @var Filesystem $filesystem
     */
    private $filesystem;


    /**
     * ReadOnlyDecorator constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }


    /**
     * @inheritDoc
     */
    public function hasDir(string $path) : bool
    {
        return $this->filesystem->hasDir($path);
    }


    /**
     * @inheritDoc
     */
    public function listContents(string $path = '', bool $recursive = false) : array
    {
        return $this->filesystem->listContents($path, $recursive);
    }


    /**
     * @inheritDoc
     */
    public function createDir(string $path, string $visibility = Visibility::PUBLIC_ACCESS)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function copyDir(string $source, string $destination)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function deleteDir(string $path)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function read(string $path) : string
    {
        return $this->filesystem->read($path);
    }


    /**
     * @inheritDoc
     */
    public function has(string $path) : bool
    {
        return $this->filesystem->has($path);
    }


    /**
     * @inheritDoc
     */
    public function getMimeType(string $path) : string
    {
        return $this->filesystem->getMimeType($path);
    }


    /**
     * @inheritDoc
     */
    public function getTimestamp(string $path) : \DateTimeImmutable
    {
        return $this->filesystem->getTimestamp($path);
    }


    /**
     * @inheritDoc
     */
    public function getSize(string $path, int $fileSizeUnit) : DataSize
    {
        return $this->filesystem->getSize(
            $path,
            $fileSizeUnit
        );
    }


    /**
     * @inheritDoc
     */
    public function setVisibility(string $path, string $visibility) : bool
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function getVisibility(string $path) : string
    {
        return $this->filesystem->getVisibility($path);
    }


    /**
     * @inheritDoc
     */
    public function readStream(string $path) : FileStream
    {
        return $this->filesystem->readStream($path);
    }


    /**
     * @inheritDoc
     */
    public function writeStream(string $path, FileStream $stream)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function putStream(string $path, FileStream $stream)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function updateStream(string $path, FileStream $stream)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function write(string $path, string $content)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function update(string $path, string $newContent)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function put(string $path, string $content)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function delete(string $path)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function readAndDelete(string $path) : string
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function rename(string $path, string $newPath)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function copy(string $path, string $copyPath)
    {
        throw new IOException("FS has ready access only");
    }
}
