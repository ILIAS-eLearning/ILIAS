<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


use ILIAS\Setup;

class ilFileSystemDirectoriesCreatedObjective implements Setup\Objective
{
    /**
     * @var	\ilFileSystemSetupConfig
     */
    protected $config;

    public function __construct(
        \ilFileSystemSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "ILIAS directories are created";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);
        $data_dir = $this->config->getDataDir();
        $web_dir = dirname(__DIR__, 4) . "/data";

        $client_data_dir = $data_dir . '/' . $client_id;
        $client_web_dir = $web_dir . '/' . $client_id;

        return [
            new ilIniFilesPopulatedObjective(),
            new Setup\Objective\DirectoryCreatedObjective($data_dir),
            new Setup\Objective\DirectoryCreatedObjective($web_dir),
            new Setup\Objective\DirectoryCreatedObjective(
                $client_web_dir
            ),
            new Setup\Objective\DirectoryCreatedObjective(
                $client_data_dir
            )
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable("clients", "datadir", $this->config->getDataDir());

        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        return $ini->readVariable("clients", "datadir") !== $this->config->getDataDir();
    }
}
