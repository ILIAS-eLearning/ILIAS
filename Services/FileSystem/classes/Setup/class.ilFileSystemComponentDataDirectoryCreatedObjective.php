<?php

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilFileSystemComponentDataDirectoryCreatedObjective extends Setup\Objective\DirectoryCreatedObjective implements Setup\Objective
{
    const DATADIR = 1;
    const WEBDIR = 2;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $component_dir;

    /**
     * @var int
     */
    protected $base_location;


    public function __construct(
        string $component_dir,
        int $base_location = self::DATADIR
    ) {
        parent::__construct($component_dir);

        $this->component_dir = $component_dir;
        $this->base_location = $base_location;
    }

    /**
     * @inheritdocs
     */
    public function getHash() : string
    {
        return hash("sha256", self::class . "::" . $this->component_dir . (string) $this->base_location);
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

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $config = $environment->getConfigFor("filesystem");
        return [
            new ilFileSystemDirectoriesCreatedObjective($config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $this->path = $this->buildPath($environment);
        return parent::achieve($environment);
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $this->path = $this->buildPath($environment);
        return parent::isApplicable($environment);
    }
}
