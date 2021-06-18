<?php

declare(strict_types=1);

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
use ILIAS\DI;

class ilTreeExistsObjective implements Setup\Objective
{
    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "The tree exists";
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment): array
    {
        return [
            new Setup\Objective\ClientIdReadObjective(),
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilComponentPluginAdminInitObjective()
        ];
    }

    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $ORIG_DIC = $this->initEnvironment($environment);

        $tree = new ilTree(ROOT_FOLDER_ID);

        $GLOBALS["DIC"] = $ORIG_DIC;

        return $environment->withResource(Setup\Environment::RESOURCE_TREE, $tree);
    }

    public function isApplicable(Setup\Environment $environment): bool
    {
        return true;
    }

    protected function initEnvironment(Setup\Environment $environment): ?ILIAS\DI\Container
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $plugin_admin = $environment->getResource(Setup\Environment::RESOURCE_PLUGIN_ADMIN);
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["DIC"]["ilIliasIniFile"] = $ini;
        $GLOBALS["DIC"]["ilClientIniFile"] = $client_ini;
        $GLOBALS["DIC"]["ilLog"] = new class () extends ilLogger {
            public function __construct()
            {
            }
            public function write(string $a_message, $a_level = ilLogLevel::INFO): void
            {
            }
            public function info(string $a_message): void
            {
            }
            public function warning(string $a_message): void
            {
            }
            public function error(string $a_message): void
            {
            }
            public function debug(string $a_message, array $a_context = []): void
            {
            }
            public function dump($a_variable, int $a_level = ilLogLevel::INFO): void
            {
            }
        };
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class () extends ilLoggerFactory {
            public function __construct()
            {
            }
            public static function getRootLogger(): ilLogger
            {
                return $GLOBALS["DIC"]["ilLog"];
            }
            public static function getLogger(string $a_component_id): ilLogger
            {
                return $GLOBALS["DIC"]["ilLog"];
            }
        };


        if (!defined('DEBUG')) {
            define('DEBUG', false);
        }

        if (!defined('ILIAS_LOG_DIR')) {
            define("ILIAS_LOG_DIR", $ini->readVariable("log", "path"));
        }
        if (!defined('ILIAS_LOG_FILE')) {
            define("ILIAS_LOG_FILE", $ini->readVariable("log", "file"));
        }
        if (!defined('ILIAS_LOG_ENABLED')) {
            define("ILIAS_LOG_ENABLED", $ini->readVariable("log", "enabled"));
        }
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define("ILIAS_ABSOLUTE_PATH", $ini->readVariable("server", "absolute_path"));
        }
        if (!defined('ROOT_FOLDER_ID')) {
            define("ROOT_FOLDER_ID", $client_ini->readVariable('system', 'ROOT_FOLDER_ID'));
        }

        $GLOBALS["ilLog"] = $GLOBALS["DIC"]["ilLog"];
        $GLOBALS["DIC"]["ilBench"] = null;
        $GLOBALS["DIC"]["lng"] = new ilLanguage('en');
        $GLOBALS["DIC"]["ilPluginAdmin"] = $plugin_admin;
        $GLOBALS["DIC"]["ilObjDataCache"] = new ilObjectDataCache();
        $GLOBALS["DIC"]["ilSetting"] = new ilSetting();
        $GLOBALS["DIC"]["objDefinition"] = new ilObjectDefinition();
        $GLOBALS["DIC"]["ilUser"] = new class () extends ilObjUser {
            public array $prefs = [];
            public function __construct()
            {
                $this->prefs['language'] = 'en';
            }
        };

        return $DIC;
    }
}
