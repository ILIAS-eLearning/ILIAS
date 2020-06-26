<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\DI;

class ilDatabaseUpdatedObjective extends \ilDatabaseObjective
{
    /**
     * @var	ilDatabaseSetupConfig
     */
    protected $config;

    /**
     * @var	bool
     */
    protected $populate_before;

    public function __construct(\ilDatabaseSetupConfig $config, bool $populate_before = false)
    {
        parent::__construct($config);
        $this->populate_before = $populate_before;
    }

    public function getHash() : string
    {
        return hash("sha256", implode("-", [
            self::class,
            $this->config->getHost(),
            $this->config->getPort(),
            $this->config->getDatabase()
        ]));
    }

    public function getLabel() : string
    {
        return "The database is updated.";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $common_config = $environment->getConfigFor("common");
        if (!$this->populate_before) {
            return [
                new \ilIniFilesLoadedObjective($common_config),
                new \ilDatabaseExistsObjective($this->config)
            ];
        }
        return [
            new \ilIniFilesPopulatedObjective($common_config),
            new \ilDatabasePopulatedObjective($this->config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $common_config = $environment->getConfigFor("common");
        $filesystem_config = $environment->getConfigFor("filesystem");

        // ATTENTION: This is a total abomination. It only exists to allow the db-
        // update to run. This is a memento to the fact, that dependency injection
        // is something we want. Currently, every component could just service
        // locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"] ?? [];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["ilDB"] = $db;
        $GLOBALS["DIC"]["ilBench"] = null;
        $GLOBALS["DIC"]["ilLog"] = new class($io) {
            public function __construct($io)
            {
                $this->io = $io;
            }
            public function write()
            {
            }
            public function info()
            {
            }
            public function warning($msg)
            {
                $this->io->inform($msg);
            }
            public function error($msg)
            {
                throw new Setup\UnachievableException(
                    "Problem in DB-Update: $msg"
                );
            }
        };
        $GLOBALS["ilLog"] = $GLOBALS["DIC"]["ilLog"];
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class() {
            public function getRootLogger()
            {
                return new class() {
                    public function write()
                    {
                    }
                };
            }
        };
        $GLOBALS["ilCtrlStructureReader"] = new class() {
            public function getStructure()
            {
            }
            public function setIniFile()
            {
            }
        };
        define("CLIENT_DATA_DIR", $filesystem_config->getDataDir() . "/" . $common_config->getClientId());
        define("CLIENT_WEB_DIR", $filesystem_config->getWebDir() . "/" . $common_config->getClientId());
        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }
        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", false);
        }
        define("ROOT_FOLDER_ID", $client_ini->readVariable("system", "ROOT_FOLDER_ID"));
        define("ROLE_FOLDER_ID", $client_ini->readVariable("system", "ROLE_FOLDER_ID"));
        define("SYSTEM_FOLDER_ID", $client_ini->readVariable("system", "SYSTEM_FOLDER_ID"));


        $db_update = new class($db, $client_ini) extends ilDBUpdate {
            public function loadXMLInfo()
            {
            }
        };

        $db_update->applyUpdate();
        $db_update->applyHotfix();
        $db_update->applyCustomUpdates();

        return $environment;
    }
}
