<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\DI;
use ILIAS\Setup\Objective\ClientIdReadObjective;

class ilComponentUpdatePluginObjective implements Setup\Objective
{
    /**
     * @var string
     */
    protected $plugin_name;

    public function __construct(string $plugin_name)
    {
        $this->plugin_name = $plugin_name;
    }

    /**
     * @inheritdoc
     */
    public function getHash() : string
    {
        return hash("sha256", self::class . $this->plugin_name);
    }

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return "Update plugin $this->plugin_name.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ClientIdReadObjective(),
            new \ilIniFilesLoadedObjective(),
            new \ilDatabaseInitializedObjective(),
            new \ilComponentPluginAdminInitObjective(),
            new \ilComponentRepositoryExistsObjective(),
            new \ilComponentFactoryExistsObjective()
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $component_repository = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY);
        $component_factory = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_FACTORY);
        $info = $component_repository->getPluginByName($this->plugin_name);

        if (!$info->supportsCLISetup()) {
            throw new \RuntimeException(
                "Plugin $this->plugin_name does not support command line setup."
            );
        }

        if ($info->isUpdateRequired()) {
            throw new \RuntimeException(
                "Plugin $this->plugin_name is already updated."
            );
        }

        [$ORIG_DIC, $ORIG_ilDB] = $this->initEnvironment($environment);
        $plugin = $component_factory->getPlugin($info->getId());
        $plugin->update();
        $GLOBALS["DIC"] = $ORIG_DIC;
        $GLOBALS["ilDB"] = $ORIG_ilDB;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $component_repository = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY);
        $plugin = $component_repository->getPluginByName($this->plugin_name);

        return $plugin->isUpdateRequired();
    }

    protected function initEnvironment(Setup\Environment $environment) : array
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $plugin_admin = $environment->getResource(Setup\Environment::RESOURCE_PLUGIN_ADMIN);
        $ini = $environment->getResource(Setup\Environment::RESOURCE_ILIAS_INI);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);


        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $ORIG_DIC = $GLOBALS["DIC"];
        $ORIG_ilDB = $GLOBALS["ilDB"];

        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["ilDB"] = $db;
        $GLOBALS["DIC"]["ilIliasIniFile"] = $ini;
        $GLOBALS["DIC"]["ilClientIniFile"] = $client_ini;
        $GLOBALS["DIC"]["ilLogger"] = new class() extends ilLogger {
            public function __construct()
            {
            }
            public function isHandling(int $a_level) : bool
            {
                return true;
            }
            public function log(string $a_message, int $a_level = ilLogLevel::INFO) : void
            {
            }
            public function dump($a_variable, int $a_level = ilLogLevel::INFO) : void
            {
            }
            public function debug(string $a_message, array $a_context = array()) : void
            {
            }
            public function info(string $a_message) : void
            {
            }
            public function notice(string $a_message) : void
            {
            }
            public function warning(string $a_message) : void
            {
            }
            public function error(string $a_message) : void
            {
            }
            public function critical(string $a_message) : void
            {
            }
            public function alert(string $a_message) : void
            {
            }
            public function emergency(string $a_message) : void
            {
            }
            public function write(string $a_message, $a_level = ilLogLevel::INFO) : void
            {
            }
            public function writeLanguageLog(string $a_topic, string $a_lang_key) : void
            {
            }
            public function logStack(?int $a_level = null, string $a_message = '') : void
            {
            }
            public function writeMemoryPeakUsage(int $a_level) : void
            {
            }
        };
        $GLOBALS["DIC"]["ilLog"] = new class() extends ilLog {
            public function __construct()
            {
            }
            public function write(string $a_msg, $a_log_level = ilLogLevel::INFO) : void
            {
            }
            public function info($msg) : void
            {
            }
            public function warning($msg) : void
            {
            }
            public function error($msg) : void
            {
            }
            public function debug($msg, $a = []) : void
            {
            }
            public function dump($a_var, ?int $a_log_level = ilLogLevel::INFO) : void
            {
            }
        };
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class() extends ilLoggerFactory {
            public function __construct()
            {
            }
            public static function getRootLogger() : ilLogger
            {
                return $GLOBALS["DIC"]["ilLogger"];
            }
            public static function getLogger(string $a_component_id) : ilLogger
            {
                return $GLOBALS["DIC"]["ilLogger"];
            }
        };
        $GLOBALS["ilLog"] = $GLOBALS["DIC"]["ilLog"];
        $GLOBALS["DIC"]["ilBench"] = null;
        $GLOBALS["DIC"]["lng"] = new ilLanguage('en');
        $GLOBALS["DIC"]["ilPluginAdmin"] = $plugin_admin;
        $GLOBALS["DIC"]["ilias"] = null;
        $GLOBALS["DIC"]["ilErr"] = null;
        $GLOBALS["DIC"]["tree"] = new class() extends ilTree {
            public function __construct()
            {
            }
        };
        $GLOBALS["DIC"]["ilAppEventHandler"] = new class() extends ilAppEventHandler {
            public function __construct()
            {
            }
            public function raise($a_component, $a_event, $a_parameter = "") : void
            {
            }
        };
        $GLOBALS["DIC"]["ilObjDataCache"] = new ilObjectDataCache();
        $GLOBALS["DIC"]["ilSetting"] = new ilSetting();
        $GLOBALS["DIC"]["objDefinition"] = new ilObjectDefinition();
        $GLOBALS["DIC"]["rbacadmin"] = new class() extends ilRbacAdmin {
            public function __construct()
            {
            }
        };
        $GLOBALS["DIC"]["rbacreview"] = new class() extends ilRbacReview {
            public function __construct()
            {
            }
        };
        $GLOBALS["DIC"]["ilUser"] = new class() extends ilObjUser {
            public array $prefs = [];

            public function __construct()
            {
                $this->prefs["language"] = "en";
            }
        };

        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 5));
        }

        if (!defined('SYSTEM_ROLE_ID')) {
            define('SYSTEM_ROLE_ID', '2');
        }

        if (!defined("CLIENT_ID")) {
            define('CLIENT_ID', $client_ini->readVariable('client', 'name'));
        }

        if (!defined("ILIAS_WEB_DIR")) {
            define('ILIAS_WEB_DIR', dirname(__DIR__, 4) . "/data/");
        }

        return [$ORIG_DIC, $ORIG_ilDB];
    }
}
