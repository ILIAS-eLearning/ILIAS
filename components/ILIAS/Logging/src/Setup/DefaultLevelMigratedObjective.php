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

declare(strict_types=1);

namespace ILIAS\Logging\Setup;

use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Config;
use ILIAS\Setup\UnachievableException;
use ilIniFilesLoadedObjective;
use ilSettingsFactoryExistsObjective;
use ilIniFile;
use ilSettingsFactory;
use ILIAS\Logging\ILIASLogLevel;

class DefaultLevelMigratedObjective implements Objective
{
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Move default log level for ILIAS\Logging from database to ilias.ini.php";
    }

    public function isNotable(): bool
    {
        return false;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilSettingsFactoryExistsObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        /** @var ilIniFile $ini */
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        /** @var ilSettingsFactory $settings_factory */
        $settings_factory = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);
        $settings = $settings_factory->settingsFor("logging");

        if (!$ini->groupExists("log")) {
            return $environment;
        }

        // Default is INFO, if settings were never saved
        $level = ILIASLogLevel::tryFrom((int) $settings->get("level", "")) ?? ILIASLogLevel::INFO;
        $ini->setVariable("log", "default_level", $level->toString());

        $settings->delete("level");
        /*
         * It would be better to also delete the long defunct "level" field in the
         * ilias.ini.php, but deleting fields is not supported by ilIniFile.
         */

        if (!$ini->write()) {
            throw new UnachievableException("Could not write ilias.ini.php");
        }

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        $ini = $environment->getResource(Environment::RESOURCE_ILIAS_INI);
        return !$ini->variableExists("log", "default_level");
    }
}
