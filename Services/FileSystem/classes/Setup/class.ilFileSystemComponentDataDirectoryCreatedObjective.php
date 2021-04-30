<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Setup\UnachievableException;

class ilFileSystemComponentDataDirectoryCreatedObjective implements Setup\Objective
{
    const DATADIR = 1;
    const WEBDIR = 2;
    const DEFAULT_DIRECTORY_PERMISSIONS = 0755;

    /**
     * @var string
     */
    protected $component_dir;

    /**
     * @var int
     */
    protected $base_location;

    /**
     * @var int
     */
    protected $permissions;

    public function __construct(
        string $component_dir,
        int $base_location = self::DATADIR,
        int $permissions = self::DEFAULT_DIRECTORY_PERMISSIONS
    ) {
        $this->component_dir = $component_dir;
        $this->base_location = $base_location;
        $this->permissions = $permissions;
    }

    /**
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash("sha256", self::class . "::" . $this->component_dir . (string) $this->base_location);
    }

    /**
     * @inheritdocs
     */
    public function getLabel() : string
    {
        $dir = '';
        if ($this->base_location === self::DATADIR) {
            $dir = 'data';
        }
        if ($this->base_location === self::WEBDIR) {
            $dir = 'web';
        }

        return "Create $dir directory in component directory $this->component_dir";
    }

    /**
    * @inheritdocs
    */
    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $config = $environment->getConfigFor("filesystem");
        return [
            new ilFileSystemDirectoriesCreatedObjective($config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $path = $this->buildPath($environment);

        if (!file_exists($path)) {
            mkdir($path, $this->permissions);
        }
        if (!is_dir($path)) {
            throw new UnachievableException(
                "Could not create directory '{$path}'"
            );
        }
        return $environment;
    }

    protected function buildPath(Setup\Environment $environment) : string
    {
        $common_config = $environment->getConfigFor("common");
        $fs_config = $environment->getConfigFor("filesystem");

        if ($this->base_location === self::DATADIR) {
            $data_dir = $fs_config->getDataDir();
        }

        if ($this->base_location === self::WEBDIR) {
            $data_dir = $fs_config->getWebDir();
        }

        $client_data_dir = $data_dir . '/' . $common_config->getClientId();
        $new_dir = $client_data_dir . '/' . $this->component_dir;

        return $new_dir;
    }
}
