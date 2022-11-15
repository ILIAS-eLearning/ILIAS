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

use ILIAS\Setup;

class ilFileSystemComponentDataDirectoryCreatedObjective extends Setup\Objective\DirectoryCreatedObjective implements Setup\Objective
{
    public const DATADIR = 1;
    public const WEBDIR = 2;

    protected string $component_dir;

    protected int $base_location;


    public function __construct(
        string $component_dir,
        int $base_location = self::DATADIR
    ) {
        parent::__construct($component_dir);

        $this->component_dir = $component_dir;
        $this->base_location = $base_location;
    }


    public function getHash(): string
    {
        return hash("sha256", self::class . "::" . $this->component_dir . $this->base_location);
    }

    protected function buildPath(Setup\Environment $environment): ?string
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);

        if ($ini === null || $client_id === null) {
            return null;
        }

        if ($this->base_location === self::DATADIR) {
            $data_dir = $ini->readVariable('clients', 'datadir');
        } elseif ($this->base_location === self::WEBDIR) {
            $data_dir = dirname(__DIR__, 4) . "/data";
        }
        if (!isset($data_dir)) {
            throw new LogicException('cannot determine base directory');
        }

        $client_data_dir = $data_dir . '/' . $client_id;
        $new_dir = $client_data_dir . '/' . $this->component_dir;
        return $new_dir;
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
                new ilFileSystemDirectoriesCreatedObjective($config)
            ];
        }

        // case if ILIAS is already installed
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $this->path = $this->buildPath($environment);
        return parent::achieve($environment);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        if (($path = $this->buildPath($environment)) === null) {
            return false;
        }
        $this->path = $path;
        return parent::isApplicable($environment);
    }
}
