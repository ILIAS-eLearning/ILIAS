<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler\FileSystemBased;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\UniqueIDIdentificationGenerator;
use ILIAS\ResourceStorage\Resource\StorableResource;
use ILIAS\ResourceStorage\Revision\CloneRevision;
use ILIAS\ResourceStorage\Revision\FileStreamRevision;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Revision\UploadedFileRevision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\StorageHandler\PathGenerator\PathGenerator;

/**
 * Class AbstractFileSystemStorageHandler
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractFileSystemStorageHandler implements StorageHandler
{
    protected const DATA = 'data';
    /**
     * @var PathGenerator
     */
    protected $path_generator;
    /**
     * @var Filesystem
     */
    protected $fs;
    /**
     * @var UniqueIDIdentificationGenerator
     */
    protected $id;
    /**
     * @var int
     */
    protected $location;
    /**
     * @var bool
     */
    protected $links_possible = false;

    public function __construct(
        Filesystem $filesystem,
        int $location = Location::STORAGE,
        bool $determine_linking_possible = false
    ) {
        $this->fs = $filesystem;
        $this->location = $location;
        $this->id = new UniqueIDIdentificationGenerator();
        if ($determine_linking_possible) {
            $this->links_possible = $this->determineLinksPossible();
        }
    }

    private function determineLinksPossible() : bool
    {
        $random_filename = "test_" . random_int(10000, 99999);

        $original_filename = $this->getStorageLocationBasePath() . "/" . $random_filename . '_orig';
        $linked_filename = $this->getStorageLocationBasePath() . "/" . $random_filename . '_link';
        $cleaner = function () use ($original_filename, $linked_filename) {
            try {
                $this->fs->delete($original_filename);
            } catch (\Throwable $t) {
            }
            try {
                $this->fs->delete($linked_filename);
            } catch (\Throwable $t) {
            }
        };

        try {
            // remove existing files
            $cleaner();

            // create file
            $this->fs->write($original_filename, 'data');
            $stream = $this->fs->readStream($original_filename);

            // determine absolute pathes
            $original_absolute_path = $stream->getMetadata('uri');
            $linked_absolute_path = dirname($original_absolute_path) . "/" . $random_filename . '_link';

            // try linking and unlinking
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $linking = @link($original_absolute_path, $linked_absolute_path);
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            $unlinking = @unlink($original_absolute_path);
            $stream->close();
            if ($linking && $unlinking && $this->fs->has($linked_filename)) {
                $cleaner();

                return true;
            }
            $cleaner();
        } catch (\Throwable $t) {
            return false;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getIdentificationGenerator() : IdentificationGenerator
    {
        return $this->id;
    }

    public function has(ResourceIdentification $identification) : bool
    {
        return $this->fs->has($this->getFullContainerPath($identification));
    }

    /**
     * @inheritDoc
     */
    public function getStream(Revision $revision) : FileStream
    {
        return $this->fs->readStream($this->getRevisionPath($revision) . '/' . self::DATA);
    }

    public function storeUpload(UploadedFileRevision $revision) : bool
    {
        global $DIC;

        $DIC->upload()->moveOneFileTo($revision->getUpload(), $this->getRevisionPath($revision), $this->location,
            self::DATA);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function storeStream(FileStreamRevision $revision) : bool
    {
        try {
            if ($revision->keepOriginal()) {
                $stream = $revision->getStream();
                $this->fs->writeStream($this->getRevisionPath($revision) . '/' . self::DATA, $stream);
                $stream->close();
            } else {
                $target = $revision->getStream()->getMetadata('uri');
                if ($this->links_possible) {
                    $this->fs->createDir($this->getRevisionPath($revision));
                    link($target, $this->getAbsoluteRevisionPath($revision) . '/' . self::DATA);
                    unlink($target);
                } else {
                    $this->fs->rename(LegacyPathHelper::createRelativePath($target),
                        $this->getRevisionPath($revision) . '/' . self::DATA);
                }
                $revision->getStream()->close();
            }
        } catch (\Throwable $t) {
            return false;
        }

        return true;
    }

    public function cloneRevision(CloneRevision $revision) : bool
    {
        $stream = $this->getStream($revision->getRevisionToClone());
        try {
            $this->fs->writeStream($this->getRevisionPath($revision) . '/' . self::DATA, $stream);
            $stream->close();
        } catch (\Throwable $t) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteRevision(Revision $revision) : void
    {
        try {
            $this->fs->deleteDir($this->getRevisionPath($revision));
        } catch (\Throwable $t) {

        }

    }

    /**
     * @inheritDoc
     */
    public function deleteResource(StorableResource $resource) : void
    {
        try {
            $this->fs->deleteDir($this->getFullContainerPath($resource->getIdentification()));
        } catch (\Throwable $t) {
        }
        try {
            $this->cleanUpContainer($resource);
        } catch (\Throwable $t) {
        }

    }

    public function cleanUpContainer(StorableResource $resource) : void
    {
        $storage_path = $this->getStorageLocationBasePath();
        $container_path = $this->getContainerPathWithoutBase($resource->getIdentification());
        $first_level = strtok($container_path, "/");
        if (!empty($first_level)) {
            $full_first_level = $storage_path . '/' . $first_level;
            $number_of_files = $this->fs->finder()->files()->in([$full_first_level])->count();
            if ($number_of_files === 0) {
                $this->fs->deleteDir($full_first_level);
            }
        }
    }

    public function getBasePath(ResourceIdentification $identification) : string
    {
        return $this->getFullContainerPath($identification);
    }

    public function getRevisionPath(Revision $revision) : string
    {
        return $this->getFullContainerPath($revision->getIdentification()) . '/' . $revision->getVersionNumber();
    }

    public function getFullContainerPath(ResourceIdentification $identification) : string
    {
        return $this->getStorageLocationBasePath() . '/' . $this->getContainerPathWithoutBase($identification);
    }

    public function getContainerPathWithoutBase(ResourceIdentification $identification) : string
    {
        return $this->path_generator->getPathFor($identification);
    }

    private function getAbsoluteRevisionPath(Revision $revision) : string
    {
        $str = rtrim(CLIENT_DATA_DIR, "/") . "/" . ltrim($this->getRevisionPath($revision), "/");
        return $str;
    }

    public function movementImplementation() : string
    {
        return $this->links_possible ? 'link' : 'rename';
    }

}
