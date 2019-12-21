<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Provider\FlySystem;

;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\MetadataType;
use ILIAS\Filesystem\Provider\DirectoryAccess;
use ILIAS\Filesystem\Visibility;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\RootViolationException;

/**
 * Class FlySystemDirectoryAccess
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
class FlySystemDirectoryAccess implements DirectoryAccess
{

    /**
     * @var FilesystemInterface $flySystemFS
     */
    private $flySystemFS;
    /**
     * @var FlySystemFileAccess $fileAccess
     */
    private $fileAccess;
    private static $metaTypeKey = 'type';
    private static $metaPathKey = 'path';


    /**
     * FlySystemDirectoryAccess constructor.
     *-
     * @param FilesystemInterface $flySystemFS      A configured fly system filesystem instance.
     * @param FlySystemFileAccess $fileAccess       The file access implementation used to copy files.
     */
    public function __construct(FilesystemInterface $flySystemFS, FlySystemFileAccess $fileAccess)
    {
        $this->flySystemFS = $flySystemFS;
        $this->fileAccess = $fileAccess;
    }


    /**
     * Checks whether the directory exists or not.
     *
     * @param string $path The path which should be checked.
     *
     * @return bool         True if the directory exists otherwise false.
     *
     * @throws IOException  Thrown if the type of the path element is unknown.
     *
     * @since   5.3
     * @version 1.0
     */
    public function hasDir(string $path) : bool
    {
        if ($this->flySystemFS->has($path)) {
            $meta = $this->flySystemFS->getMetadata($path);

            if (!(is_array($meta) && array_key_exists(self::$metaTypeKey, $meta))) {
                throw new IOException("Could not evaluate path type: \"$path\"");
            }

            return strcmp($meta[self::$metaTypeKey], MetadataType::DIRECTORY) === 0;
        }

        return false;
    }


    /**
     * Lists the content of a directory.
     *
     * @param string $path                  The directory which should listed. Defaults to the adapter root directory.
     * @param bool   $recursive             Set to true if the child directories also should be listed. Defaults to false.
     *
     * @return Metadata[]                   An array of metadata about all known files, in the given directory.
     *
     * @throws DirectoryNotFoundException   If the directory is not found or inaccessible.
     * @throws IOException                  Thrown if the metadata can not be fetched for a path.
     *
     * @since   5.3
     * @version 1.0
     */
    public function listContents(string $path = '', bool $recursive = false) : array
    {
        if ($path !== '') {
            $this->ensureDirectoryExistence($path);
        }

        $contents = $this->flySystemFS->listContents($path, $recursive);
        $metadataCollection = [];

        foreach ($contents as $content) {
            if (!(array_key_exists(self::$metaTypeKey, $content) && array_key_exists(self::$metaPathKey, $content))) {
                throw new IOException("Invalid metadata received for path \"$path\"");
            }

            $metadataCollection[] = $this->arrayToMetadata($content);
        }
        
        return $metadataCollection;
    }


    /**
     * Create a new directory.
     *
     * Please note that the Visibility interface defines two constants PUBLIC_ACCESS and PRIVATE_ACCESS
     * to ease the development process.
     *
     * @param string $path       The directory path which should be created.
     * @param string $visibility The visibility of the directory. Defaults to visibility public.
     *
     * @return void
     *
     * @throws IOException                   If the directory could not be created.
     * @throws \InvalidArgumentException     If the visibility is not 'public' or 'private'.
     *
     * @since   5.3
     * @version 1.0
     */
    public function createDir(string $path, string $visibility = Visibility::PUBLIC_ACCESS)
    {
        $this->validateVisibility($visibility);

        $config = ['visibility' => $visibility];
        $successful = $this->flySystemFS->createDir($path, $config);

        if (!$successful) {
            throw new IOException("Could not create directory \"$path\"");
        }
    }


    /**
     * Copy all childes of the source recursive to the destination.
     * The file access rights will be copied as well.
     *
     * The operation will fail fast if the destination directory is not empty.
     * All destination folders will be created if needed.
     *
     * @param string $source      The source which should be scanned and copied.
     * @param string $destination The destination of the recursive copy.
     *
     * @throws IOException                  Thrown if the directory could not be copied.
     * @throws DirectoryNotFoundException   Thrown if the source directory could not be found.
     *
     * @return void
     *
     * @since   5.3
     * @version 1.0
     */
    public function copyDir(string $source, string $destination)
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
                    $this->fileAccess->copy($content->getPath(), $destinationFilePath);
                }
            }
        }
    }


    /**
     * Ensures that the given path does not exist or is empty.
     *
     * @param string $path The path which should be checked.
     *
     * @throws IOException Thrown if the metadata of the path can not be fetched.
     */
    private function ensureEmptyDirectory(string $path)
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
    private function ensureDirectoryExistence(string $path)
    {
        if (!$this->hasDir($path)) {
            throw new DirectoryNotFoundException("Directory \"$path\" not found.");
        }
    }


    /**
     * Deletes a directory recursive.
     *
     * @param string $path The path which should be deleted.
     *
     * @return void
     *
     * @throws IOException If the path could not be deleted.
     *
     * @since   5.3
     * @version 1.0
     */
    public function deleteDir(string $path)
    {
        try {
            if ($this->flySystemFS->deleteDir($path) === false) {
                throw new IOException("Could not delete directory \"$path\".");
            }
        } catch (RootViolationException $ex) {
            throw new IOException('The filesystem root must not be deleted.', 0, $ex);
        }
    }


    /**
     * Parses a metadata array into a metadata object.
     * Array example:
     *  [
     *     'type' => 'dir' / 'file',
     *     'path' => '/path/to/your/dir-or-file'
     *  ]
     *
     * @param array $metadataArray
     *
     * @return Metadata
     */
    private function arrayToMetadata(array $metadataArray)
    {
        return new Metadata(
            $metadataArray[self::$metaPathKey],
            $metadataArray[self::$metaTypeKey]
        );
    }


    /**
     * Validates if the given visibility is known, otherwise an exception is thrown.
     * This method does nothing if the visibility is valid.
     *
     * @param string $visibility The visibility which should be validated.
     * @return void
     */
    private function validateVisibility($visibility)
    {
        if (strcmp($visibility, Visibility::PRIVATE_ACCESS) !== 0 && strcmp($visibility, Visibility::PUBLIC_ACCESS) !== 0) {
            throw new \InvalidArgumentException("Invalid visibility expected public or private but got \"$visibility\".");
        }
    }
}
