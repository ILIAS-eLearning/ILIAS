<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler\FileSystemBased;

use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\StorageHandler\PathGenerator\UUIDBasedPathGenerator;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Class FileSystemStorageHandler
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated Use MaxNestingFileSystemStorageHandler instead
 */
class FileSystemStorageHandler extends AbstractFileSystemStorageHandler
{
    public function __construct(
        Filesystem $filesystem,
        int $location = Location::STORAGE,
        bool $determine_linking_possible = false
    ) {
        parent::__construct($filesystem, $location, $determine_linking_possible);
        $this->path_generator = new UUIDBasedPathGenerator();
    }

    /**
     * @inheritDoc
     */
    public function getID() : string
    {
        return 'fsv1';
    }

    public function getStorageLocationBasePath() : string
    {
        return StorageHandlerFactory::BASE_DIRECTORY;
    }

    public function isPrimary() : bool
    {
        return false;
    }

}
