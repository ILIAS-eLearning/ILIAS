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

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Provider\DirectoryAccess;
use ILIAS\Filesystem\Visibility;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\FileAttributes;
use League\Flysystem\StorageAttributes;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
class FlySystemDirectoryAccess implements DirectoryAccess
{
    private const KEY_TYPE = 'type';
    private const KEY_PATH = 'path';

    public function __construct(
        private FilesystemOperator $flysystem_operator,
        private FlySystemFileAccess $flysystem_access
    ) {
    }

    public function hasDir(string $path): bool
    {
        return $this->flysystem_operator->directoryExists($path);
    }

    /**
     * @return Metadata[]                   An array of metadata about all known files, in the given directory.
     */
    public function listContents(string $path = '', bool $recursive = false): array
    {
        if ($path !== '') {
            $this->ensureDirectoryExistence($path);
        }

        /**
         * @var $contents FileAttributes[]
         */
        $contents = $this->flysystem_operator->listContents($path, $recursive);
        $metadata_collection = [];

        foreach ($contents as $content) {
            $metadata_collection[] = $this->attributesToMetadata($content);
        }

        return $metadata_collection;
    }

    /**
     * Please note that the Visibility interface defines two constants PUBLIC_ACCESS and PRIVATE_ACCESS
     * to ease the development process.
     */
    public function createDir(string $path, string $visibility = Visibility::PUBLIC_ACCESS): void
    {
        $this->validateVisibility($visibility);

        $config = ['visibility' => $visibility];
        try {
            $this->flysystem_operator->createDirectory($path, $config);
        } catch (UnableToCreateDirectory) {
            throw new IOException("Could not create directory \"$path\"");
        }
    }

    /**
     * Copy all childes of the source recursive to the destination.
     * The file access rights will be copied as well.
     *
     * The operation will fail fast if the destination directory is not empty.
     * All destination folders will be created if needed.
     */
    public function copyDir(string $source, string $destination): void
    {
        $this->ensureDirectoryExistence($source);
        $this->ensureEmptyDirectory($destination);

        $content_list = $this->listContents($source, true);

        //foreach file and dir
        foreach ($content_list as $content) {
            //ignore the directories and only copy the files
            if ($content->isFile()) {
                //create destination path
                $position = strpos($content->getPath(), $source);
                if ($position !== false) {
                    $destinationFilePath = substr_replace(
                        $content->getPath(),
                        $destination,
                        $position,
                        strlen($source)
                    );
                    $this->flysystem_access->copy($content->getPath(), $destinationFilePath);
                }
            }
        }
    }

    /**
     * Ensures that the given path does not exist or is empty.
     */
    private function ensureEmptyDirectory(string $path): void
    {
        // check if destination dir is empty
        try {
            $destination_content = $this->listContents($path, true);
            if ($destination_content !== []) {
                throw new IOException("Destination \"$path\" is not empty can not copy files.");
            }
        } catch (UnableToRetrieveMetadata) {
            //nothing needs to be done the destination was not found
        }
    }

    /**
     * Checks if the directory exists.
     * If the directory was found no further actions are taken.
     */
    private function ensureDirectoryExistence(string $path): void
    {
        if (!$this->hasDir($path)) {
            throw new DirectoryNotFoundException("Directory \"$path\" not found.");
        }
    }

    public function deleteDir(string $path): void
    {
        try {
            $this->flysystem_operator->deleteDirectory($path);
        } catch (UnableToRetrieveMetadata) {
            throw new IOException("Could not find directory \"$path\".");
        } catch (UnableToDeleteDirectory|\Throwable) {
            throw new IOException("Could not delete directory \"$path\".");
        }
        if ($this->flysystem_operator->has($path)) {
            throw new IOException("Could not find directory \"$path\".");
        }
    }

    private function attributesToMetadata(StorageAttributes $attributes): \ILIAS\Filesystem\DTO\Metadata
    {
        return new Metadata(
            $attributes->path(),
            $attributes->type()
        );
    }

    /**
     * Validates if the given visibility is known, otherwise an exception is thrown.
     * This method does nothing if the visibility is valid.
     */
    private function validateVisibility(string $visibility): void
    {
        if (strcmp($visibility, Visibility::PRIVATE_ACCESS) === 0) {
            return;
        }
        if (strcmp($visibility, Visibility::PUBLIC_ACCESS) === 0) {
            return;
        }
        throw new \InvalidArgumentException("Invalid visibility expected public or private but got \"$visibility\".");
    }
}
