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

use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\Setup;
use ILIAS\Setup\Objective;

/**
 * Class ilStorageContainersExistingObjective
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilStorageContainersExistingObjective extends Objective\DirectoryCreatedObjective
{
    protected string $base_dir = StorageHandlerFactory::BASE_DIRECTORY;

    /**
     * @var string[]
     */
    protected array $storage_handler_ids = [
        'fsv2'
    ];

    public function __construct(array $storage_handler_ids = null)
    {
        parent::__construct(StorageHandlerFactory::BASE_DIRECTORY);
        $this->storage_handler_ids = $storage_handler_ids ?? $this->storage_handler_ids;
    }

    protected function buildStorageBasePath(Setup\Environment $environment): string
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        return $ini->readVariable(
            'clients',
            'datadir'
        ) . '/' . $client_id . '/' . $this->base_dir;
    }

    /**
     * @return \ilFileSystemDirectoriesCreatedObjective[]|\ilIniFilesLoadedObjective[]
     */
    public function getPreconditions(Setup\Environment $environment): array
    {
        // case if it is a fresh ILIAS installation
        if ($environment->hasConfigFor("filesystem")) {
            $config = $environment->getConfigFor("filesystem");
            return [
                new \ilFileSystemDirectoriesCreatedObjective($config)
            ];
        }

        // case if ILIAS is already installed
        return [
            new \ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $base_path = $this->buildStorageBasePath($environment);
        $this->path = $base_path;
        $environment = parent::achieve($environment);

        foreach ($this->storage_handler_ids as $storage_handler_id) {
            $this->path = $base_path . '/' . $storage_handler_id;
            $environment = parent::achieve($environment);
        }
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $base_path = $this->buildStorageBasePath($environment);
        $this->path = $base_path;
        if (parent::isApplicable($environment)) {
            return true;
        }

        foreach ($this->storage_handler_ids as $storage_handler_id) {
            $this->path = $base_path . '/' . $storage_handler_id;
            if (parent::isApplicable($environment)) {
                return true;
            }
        }
        return false;
    }
}
