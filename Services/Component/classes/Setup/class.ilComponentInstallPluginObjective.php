<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\DI;
use ILIAS\Setup\Objective\ClientIdReadObjective;

class ilComponentInstallPluginObjective implements Setup\Objective
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
        return "Install plugin $this->plugin_name.";
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
            new \ilIniFilesPopulatedObjective(),
            new \ilDatabaseUpdatedObjective(),
            new \ilComponentPluginAdminInitObjective()
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ORIG_DIC = $this->initEnvironment($environment);

        $plugin = $GLOBALS["DIC"]["ilPluginAdmin"]->getRawPluginDataFor($this->plugin_name);

        if (!is_null($plugin) && $plugin['must_install'] && $plugin['supports_cli_setup']) {
            $pl = ilPlugin::getPluginObject(
                $plugin['component_type'],
                $plugin['component_name'],
                $plugin['slot_id'],
                $plugin['name']
            );

            $pl->install();
        }

        $GLOBALS["DIC"] = $ORIG_DIC;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        $ORIG_DIC = $this->initEnvironment($environment);

        $plugin = $GLOBALS["DIC"]["ilPluginAdmin"]->getRawPluginDataFor($this->plugin_name);

        if (is_null($plugin) || !$plugin['supports_cli_setup']) {
            return false;
        }

        $GLOBALS["DIC"] = $ORIG_DIC;

        return $plugin['must_install'];
    }

    protected function initEnvironment(Setup\Environment $environment) : ILIAS\DI\Container
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
        $GLOBALS["DIC"]["ilLog"] = new class() extends ilLogger {
            public function __construct()
            {
            }
            public function write($m, $l = ilLogLevel::INFO)
            {
            }
            public function info($msg)
            {
            }
            public function warning($msg)
            {
            }
            public function error($msg)
            {
            }
            public function debug($msg, $a = [])
            {
            }
            public function dump($msg, $a = ilLogLevel::INFO)
            {
            }
        };
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class() extends ilLoggerFactory {
            public function __construct()
            {
            }
            public static function getRootLogger()
            {
                return $GLOBALS["DIC"]["ilLog"];
            }
            public static function getLogger($a)
            {
                return $GLOBALS["DIC"]["ilLog"];
            }
        };
        $GLOBALS["ilLog"] = $GLOBALS["DIC"]["ilLog"];
        $GLOBALS["DIC"]["ilBench"] = null;
        $GLOBALS["DIC"]["lng"] = new ilLanguage('en');
        $GLOBALS["DIC"]["ilPluginAdmin"] = $plugin_admin;
        $GLOBALS["DIC"]["ilCtrl"] = new ilCtrl();
        $GLOBALS["DIC"]["ilias"] = null;
        $GLOBALS["DIC"]["ilErr"] = null;
        $GLOBALS["DIC"]["tree"] = null;
        $GLOBALS["DIC"]["ilAppEventHandler"] = null;
        $GLOBALS["DIC"]["ilSetting"] = new ilSetting();
        $GLOBALS["DIC"]["objDefinition"] = new ilObjectDefinition();
        $GLOBALS["DIC"]["ilUser"] = new class() extends ilObjUser {
            public $prefs = [];

            public function __construct()
            {
                $this->prefs["language"] = "en";
            }
        };

        if (!defined('DEBUG')) {
            define('DEBUG', false);
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

        return $DIC;
    }
}
