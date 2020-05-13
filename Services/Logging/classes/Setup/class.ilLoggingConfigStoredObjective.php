<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


use ILIAS\Setup;

class ilLoggingConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilLoggingSetupConfig
     */
    protected $config;

    public function __construct(
        \ilLoggingSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Fill ini with settings for Services/Logging";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $common_config = $environment->getConfigFor("common");
        return [
            new ilIniFilesPopulatedObjective($common_config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable("log", "enabled", $this->config->isEnabled() ? "1" : "0");
        $ini->setVariable("log", "path", dirname($this->config->getPathToLogfile()));
        $ini->setVariable("log", "file", basename($this->config->getPathToLogfile()));
        $ini->setVariable("log", "error_path", $this->config->getErrorlogDir());

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
        $enabled = $this->config->isEnabled() ? "1" : "0";

        return
            $ini->readVariable("log", "path") !== dirname($this->config->getPathToLogfile()) ||
            $ini->readVariable("log", "file") !== basename($this->config->getPathToLogfile()) ||
            $ini->readVariable("log", "error_path") !== $this->config->getErrorlogDir() ||
            $ini->readVariable("log", "enabled") !== $enabled
        ;
    }
}
