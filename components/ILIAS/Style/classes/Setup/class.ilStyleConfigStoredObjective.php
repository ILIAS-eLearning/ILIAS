<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
            "scss",
            $this->config->getPathToScss() ?? ''
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
            $ini->readVariable("tools", "lessc") !== $this->config->getPathToScss() ||
            $ini->readVariable("tools", "enable_system_styles_management") !== $manage_system_styles
        ;
    }
}
