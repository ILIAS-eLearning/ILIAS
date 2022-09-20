<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


use ILIAS\Setup;

class ilStyleConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilStyleSetupConfig
     */
    protected $config;

    public function __construct(
        \ilStyleSetupConfig $config
    ) {
        $this->config = $config;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Fill ini with settings for Services/Style";
    }

    public function isNotable(): bool
    {
        return false;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);

        $ini->setVariable(
            "tools",
            "lessc",
            $this->config->getPathToLessc() ?? ''
        );
        $ini->setVariable("tools", "enable_system_styles_management", $this->config->getManageSystemStyles() ? "1" : "0");

        if (!$ini->write()) {
            throw new Setup\UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $manage_system_styles = $this->config->getManageSystemStyles() ? "1" : "0";

        return
            $ini->readVariable("tools", "lessc") !== $this->config->getPathToLessc() ||
            $ini->readVariable("tools", "enable_system_styles_management") !== $manage_system_styles
        ;
    }
}
