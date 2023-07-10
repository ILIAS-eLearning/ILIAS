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

class ilDatabaseMetricsCollectedObjective extends Setup\Metrics\CollectedObjective
{
    /**
     * @return array<\ilDatabaseInitializedObjective|\ilIniFilesLoadedObjective>
     */
    protected function getTentativePreconditions(Setup\Environment $environment): array
    {
        return [
            new \ilIniFilesLoadedObjective(),
            new \ilDatabaseInitializedObjective()
        ];
    }

    protected function collectFrom(Setup\Environment $environment, Setup\Metrics\Storage $storage): void
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        if ($client_ini) {
            $storage->storeConfigText(
                "type",
                $client_ini->readVariable("db", "type") ?? "mysql",
                "The storage backend that is used for the database."
            );
            $storage->storeConfigText(
                "host",
                $client_ini->readVariable("db", "host"),
                "The host where the storage backend is located."
            );
            $storage->storeConfigText(
                "port",
                $client_ini->readVariable("db", "port"),
                "The port where the storage backend is located at the host."
            );
            $storage->storeConfigText(
                "name",
                $client_ini->readVariable("db", "name"),
                "The name of the database in the storage backend."
            );
            $storage->storeConfigText(
                "user",
                $client_ini->readVariable("db", "user"),
                "The user to be used for the storage backend."
            );
            $storage->storeConfigText(
                "pass",
                PHP_SAPI === 'cli' ? $client_ini->readVariable("db", "pass") : '********',
                "The password for the user for the storage backend."
            );
        }


        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        if (!$db && !$ini) {
            return;
        }
        $client_id = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_ID);

        // ATTENTION: This is a total abomination. It only exists to allow the db-
        // update to run. This is a memento to the fact, that dependency injection
        // is something we want. Currently, every component could just service
        // locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"] ?? [];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["ilDB"] = $db;
        $GLOBALS["DIC"]["ilBench"] = null;
        $GLOBALS["DIC"]["ilLog"] = new class () {
            public function write(): void
            {
            }
            public function info(): void
            {
            }
            public function warning($msg): void
            {
            }
            public function error($msg): void
            {
            }
        };
        $GLOBALS["ilLog"] = $GLOBALS["DIC"]["ilLog"];
        /** @noinspection PhpArrayIndexImmediatelyRewrittenInspection */
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class () {
            public function getRootLogger(): object
            {
                return new class () {
                    public function write(): void
                    {
                    }
                };
            }
        };
        $GLOBALS["ilCtrlStructureReader"] = new class () {
            public function getStructure(): void
            {
            }
            public function setIniFile(): void
            {
            }
        };
        if (!defined("CLIENT_DATA_DIR")) {
            define("CLIENT_DATA_DIR", $ini->readVariable("clients", "datadir") . "/" . $client_id);
        }
        if (!defined("CLIENT_WEB_DIR")) {
            define("CLIENT_WEB_DIR", dirname(__DIR__, 4) . "/data/" . $client_id);
        }
        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }
        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }
        if (!defined("ROOT_FOLDER_ID")) {
            define("ROOT_FOLDER_ID", (int) $client_ini->readVariable("system", "ROOT_FOLDER_ID"));
        }
        if (!defined("ROLE_FOLDER_ID")) {
            define("ROLE_FOLDER_ID", (int) $client_ini->readVariable("system", "ROLE_FOLDER_ID"));
        }
        if (!defined("SYSTEM_FOLDER_ID")) {
            define("SYSTEM_FOLDER_ID", (int) $client_ini->readVariable("system", "SYSTEM_FOLDER_ID"));
        }

        $db_update = new  ilDBUpdate($db);
        $db_update->readCustomUpdatesInfo(true);

        $storage->storeStableCounter(
            "custom_version",
            $db_update->getCustomUpdatesCurrentVersion() ?? 0,
            "The version of the custom database schema that is currently installed."
        );
        $storage->storeStableCounter(
            "available_custom_version",
            $db_update->getCustomUpdatesFileVersion() ?? 0,
            "The version of the custom database schema that is available in the current source."
        );
        $storage->storeStableBool(
            "custom_update_required",
            $db_update->customUpdatesAvailable(),
            "Does the database require a custom update?"
        );

        $GLOBALS["DIC"] = $DIC;
    }
}
