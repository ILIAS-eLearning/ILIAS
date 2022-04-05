<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Decorator;

use DateTime;
use ilFileUtils;
use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Finder\Finder;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Visibility;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class FilesystemWhitelistDecorator
 *
 * The filesystem white list decorator rewrites forbidden file
 * endings and delegates the rest of the operation to the concrete filesystem
 * implementation which is wrapped by the decorator.
 *
 * @package ILIAS\Filesystem\Decorator
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.0.0
 * @since   5.3.4
 */
final class FilesystemWhitelistDecorator implements Filesystem
{
    private Filesystem $filesystem;
    private FilenameSanitizer $sanitizer;


    /**
     * FilesystemWhitelistDecorator constructor.
     *
     * @param Filesystem        $filesystem
     * @param FilenameSanitizer $sanitizer
     */
    public function __construct(Filesystem $filesystem, FilenameSanitizer $sanitizer)
    {
        $this->filesystem = $filesystem;
        $this->sanitizer = $sanitizer;
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
    public function createDir(string $path, string $visibility = Visibility::PUBLIC_ACCESS) : void
    {
        $this->filesystem->createDir($path, $visibility);
    }


    /**
     * @inheritDoc
     */
    public function copyDir(string $source, string $destination) : void
    {
        $this->ensureDirectoryExistence($source);
        $this->ensureEmptyDirectory($destination);

        $contentList = $this->listContents($source, true);

        //foreach file and dir
        foreach ($contentList as $content) {

            //ignore the directories and only copy the files
            if ($content->isFile()) {

                //create destination path
                $position = strpos($content->getPath(), $source);
                if ($position !== false) {
                    $destinationFilePath = substr_replace($content->getPath(), $destination, $position, strlen($source));
                    $this->copy($content->getPath(), $destinationFilePath);
                }
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function deleteDir(string $path) : void
    {
        $this->filesystem->deleteDir($path);
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
        return $this->filesystem->setVisibility(
            $this->sanitizer->sanitize($path),
            $visibility
        );
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
    public function writeStream(string $path, FileStream $stream) : void
    {
        $this->filesystem->writeStream($this->sanitizer->sanitize($path), $stream);
    }


    /**
     * @inheritDoc
     */
    public function putStream(string $path, FileStream $stream) : void
    {
        $this->filesystem->putStream($this->sanitizer->sanitize($path), $stream);
    }


    /**
     * @inheritDoc
     */
    public function updateStream(string $path, FileStream $stream) : void
    {
        $this->filesystem->updateStream($this->sanitizer->sanitize($path), $stream);
    }


    /**
     * @inheritDoc
     */
    public function write(string $path, string $content) : void
    {
        $this->filesystem->write($this->sanitizer->sanitize($path), $content);
    }


    /**
     * @inheritDoc
     */
    public function update(string $path, string $new_content) : void
    {
        $this->filesystem->update($this->sanitizer->sanitize($path), $new_content);
    }


    /**
     * @inheritDoc
     */
    public function put(string $path, string $content) : void
    {
        $this->filesystem->put($this->sanitizer->sanitize($path), $content);
    }


    /**
     * @inheritDoc
     */
    public function delete(string $path) : void
    {
        $this->filesystem->delete($path);
    }


    /**
     * @inheritDoc
     */
    public function readAndDelete(string $path) : string
    {
        return $this->filesystem->readAndDelete($path);
    }


    /**
     * @inheritDoc
     */
    public function rename(string $path, string $new_path) : void
    {
        $this->filesystem->rename(
            $path,
            $this->sanitizer->sanitize($new_path)
        );
    }


    /**
     * @inheritDoc
     */
    public function copy(string $path, string $copy_path) : void
    {
        $this->filesystem->copy(
            $path,
            $this->sanitizer->sanitize($copy_path)
        );
    }


    /**
     * Ensures that the given path does not exist or is empty.
     *
     * @param string $path The path which should be checked.
     *
     * @throws IOException Thrown if the metadata of the path can not be fetched.
     */
    private function ensureEmptyDirectory(string $path) : void
    {

        //check if destination dir is empty
        try {
            $destinationContent = $this->listContents($path, true);
            if (count($destinationContent) !== 0) {
                throw new IOException("Destination \"$path\" is not empty can not copy files.");
            }
        } catch (DirectoryNotFoundException $ex) {
            //nothing needs to be done the destination was not found
        }
    }


    /**
     * Checks if the directory exists.
     * If the directory was found no further actions are taken.
     *
     * @param string $path The path which should be found.
     *
     * @throws DirectoryNotFoundException Thrown if the directory was not found.
     */
    private function ensureDirectoryExistence(string $path) : void
    {
        if (!$this->hasDir($path)) {
            throw new DirectoryNotFoundException("Directory \"$path\" not found.");
        }
    }

    /**
     * @inheritDoc
     */
    public function finder() : Finder
    {
        return $this->filesystem->finder();
    }
}
