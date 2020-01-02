<?php
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
    public function hasDir($path)
    {
        return $this->filesystem->hasDir($path);
    }


    /**
     * @inheritDoc
     */
    public function listContents($path = '', $recursive = false)
    {
        return $this->filesystem->listContents($path, $recursive);
    }


    /**
     * @inheritDoc
     */
    public function createDir($path, $visibility = Visibility::PUBLIC_ACCESS)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function copyDir($source, $destination)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function deleteDir($path)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function read($path)
    {
        return $this->filesystem->read($path);
    }


    /**
     * @inheritDoc
     */
    public function has($path)
    {
        return $this->filesystem->has($path);
    }


    /**
     * @inheritDoc
     */
    public function getMimeType($path)
    {
        return $this->filesystem->getMimeType($path);
    }


    /**
     * @inheritDoc
     */
    public function getTimestamp($path)
    {
        return $this->filesystem->getTimestamp($path);
    }


    /**
     * @inheritDoc
     */
    public function getSize($path, $fileSizeUnit)
    {
        return $this->filesystem->getSize(
            $path,
            $fileSizeUnit
        );
    }


    /**
     * @inheritDoc
     */
    public function setVisibility($path, $visibility)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function getVisibility($path)
    {
        return $this->filesystem->getVisibility($path);
    }


    /**
     * @inheritDoc
     */
    public function readStream($path)
    {
        return $this->filesystem->readStream($path);
    }


    /**
     * @inheritDoc
     */
    public function writeStream($path, FileStream $stream)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function putStream($path, FileStream $stream)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function updateStream($path, FileStream $stream)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function write($path, $content)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function update($path, $newContent)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function put($path, $content)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function delete($path)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function readAndDelete($path)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function rename($path, $newPath)
    {
        throw new IOException("FS has ready access only");
    }


    /**
     * @inheritDoc
     */
    public function copy($path, $copyPath)
    {
        throw new IOException("FS has ready access only");
    }
}
