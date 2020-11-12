<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilVirusScannerConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilVirusScannerSetupConfig
     */
    protected $config;

    public function __construct(
        \ilVirusScannerSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Fill ini with settings for Services/VirusScanner";
    }

    public function isNotable() : bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable("tools", "vscantype", $this->config->getVirusScanner());
        $ini->setVariable("tools", "scancommand", $this->config->getPathToScan());
        $ini->setVariable("tools", "cleancommand", $this->config->getPathToClean());

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

        return
            $ini->readVariable("tools", "vscantype") !== $this->config->getVirusScanner() ||
            $ini->readVariable("tools", "scancommand") !== $this->config->getPathToScan() ||
            $ini->readVariable("tools", "cleancommand") !== $this->config->getPathToClean()
        ;
    }
}
