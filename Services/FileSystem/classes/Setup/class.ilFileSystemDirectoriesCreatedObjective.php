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
        $common_config = $environment->getConfigFor("common");
        return [
            new ilIniFilesPopulatedObjective($common_config),
            new Setup\Objective\DirectoryCreatedObjective($this->config->getDataDir()),
            new Setup\Objective\DirectoryCreatedObjective($this->config->getWebDir()),
            new Setup\Objective\DirectoryCreatedObjective(
                $this->config->getWebDir() . "/" . $common_config->getClientId()
            ),
            new Setup\Objective\DirectoryCreatedObjective(
                $this->config->getDataDir() . "/" . $common_config->getClientId()
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
