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
    public function getID(): string
    {
        return 'fsv1';
    }

    public function getStorageLocationBasePath(): string
    {
        return StorageHandlerFactory::BASE_DIRECTORY;
    }

    public function isPrimary(): bool
    {
        return false;
    }
}
