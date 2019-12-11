<?php

namespace ILIAS\Filesystem\Decorator;

use ilFileUtils;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Visibility;

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
 * @since 5.3.4
 */
final class FilesystemWhitelistDecorator implements Filesystem
{

    /**
     * @var Filesystem $filesystem
     */
    private $filesystem;
    /**
     * @var string[] $whitelist
     */
    private $whitelist;
    /**
     * @var FilenameSanitizer $sanitizer
     */
    private $sanitizer;


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
        $this->whitelist = ilFileUtils::getValidExtensions();
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
        $this->filesystem->createDir($path, $visibility);
    }


    /**
     * @inheritDoc
     */
    public function copyDir($source, $destination)
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
    public function deleteDir($path)
    {
        $this->filesystem->deleteDir($path);
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
        $this->filesystem->setVisibility(
            $this->sanitizer->sanitize($path),
            $visibility
        );
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
        $this->filesystem->writeStream($this->sanitizer->sanitize($path), $stream);
    }


    /**
     * @inheritDoc
     */
    public function putStream($path, FileStream $stream)
    {
        $this->filesystem->putStream($this->sanitizer->sanitize($path), $stream);
    }


    /**
     * @inheritDoc
     */
    public function updateStream($path, FileStream $stream)
    {
        $this->filesystem->updateStream($this->sanitizer->sanitize($path), $stream);
    }


    /**
     * @inheritDoc
     */
    public function write($path, $content)
    {
        $this->filesystem->write($this->sanitizer->sanitize($path), $content);
    }


    /**
     * @inheritDoc
     */
    public function update($path, $newContent)
    {
        $this->filesystem->update($this->sanitizer->sanitize($path), $newContent);
    }


    /**
     * @inheritDoc
     */
    public function put($path, $content)
    {
        $this->filesystem->put($this->sanitizer->sanitize($path), $content);
    }


    /**
     * @inheritDoc
     */
    public function delete($path)
    {
        $this->filesystem->delete($path);
    }


    /**
     * @inheritDoc
     */
    public function readAndDelete($path)
    {
        return $this->filesystem->readAndDelete($path);
    }


    /**
     * @inheritDoc
     */
    public function rename($path, $newPath)
    {
        $this->filesystem->rename(
            $path,
            $this->sanitizer->sanitize($newPath)
        );
    }


    /**
     * @inheritDoc
     */
    public function copy($path, $copyPath)
    {
        $this->filesystem->copy(
            $path,
            $this->sanitizer->sanitize($copyPath)
        );
    }

    /**
     * Ensures that the given path does not exist or is empty.
     *
     * @param string $path The path which should be checked.
     *
     * @throws IOException Thrown if the metadata of the path can not be fetched.
     */
    private function ensureEmptyDirectory($path)
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
    private function ensureDirectoryExistence($path)
    {
        if (!$this->hasDir($path)) {
            throw new DirectoryNotFoundException("Directory \"$path\" not found.");
        }
    }
}
