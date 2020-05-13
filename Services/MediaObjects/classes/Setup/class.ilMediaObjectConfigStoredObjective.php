<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

/**
 * Stores configuration for the service (currently only path to ffmpeg)
 * in the according ini-field.
 */
class ilMediaObjectConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilMediaObjectSetupConfig
     */
    protected $config;

    public function __construct(
        \ilMediaObjectSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Store configuration of Services/MediaObject";
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

        $ini->setVariable("tools", "ffmpeg", $this->config->getPathToFFMPEG());

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

        return $ini->readVariable("tools", "ffmpeg") !== $this->config->getPathToFFMPEG();
    }
}
