<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilPreviewConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilPreviewSetupConfig
     */
    protected $config;

    public function __construct(
        \ilPreviewSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Store configuration of Services/Preview";
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

        $ini->setVariable("tools", "ghostscript", $this->config->getPathToGhostscript());

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

        return $ini->readVariable("tools", "ghostscript") !== $this->config->getPathToGhostscript();
    }
}
