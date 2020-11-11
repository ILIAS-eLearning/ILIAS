<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


use ILIAS\Setup;

class ilFileSystemConfigNotChangedObjective implements Setup\Objective
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
        return "Config for Filesystems did not change.";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilFileSystemDirectoriesCreatedObjective($this->config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $current = $ini->readVariable("clients", "datadir");
        $new = $this->config->getDataDir();
        if ($current !== $new) {
            throw new Setup\UnachievableException(
                "You seem to try to move the ILIAS data-directory from '$current' " .
                "to '$new', the client.ini.php contains a different path then the " .
                "config you are using. This is not supported by the setup."
            );
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
