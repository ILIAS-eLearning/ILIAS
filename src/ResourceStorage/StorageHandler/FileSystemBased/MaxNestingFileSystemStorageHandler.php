<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler\FileSystemBased;

use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\StorageHandler\PathGenerator\MaxNestingPathGenerator;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * Class MaxNestingFileSystemStorageHandler
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @package ILIAS\ResourceStorage\Storage
 * @internal
 */
class MaxNestingFileSystemStorageHandler extends AbstractFileSystemStorageHandler
{
    public function __construct(
        Filesystem $filesystem,
        int $location = Location::STORAGE,
        bool $determine_linking_possible = false
    ) {
        parent::__construct($filesystem, $location, $determine_linking_possible);
        $this->path_generator = new MaxNestingPathGenerator();
    }

    /**
     * @inheritDoc
     */
    public function getID() : string
    {
        return 'fsv2';
    }

    public function getStorageLocationBasePath() : string
    {
        return StorageHandlerFactory::BASE_DIRECTORY . '/' . $this->getID();
    }

    public function isPrimary() : bool
    {
        return true;
    }

}
