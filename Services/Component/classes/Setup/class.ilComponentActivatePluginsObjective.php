<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\DI;

class ilComponentActivatePluginsObjective implements Setup\Objective
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
        return "Activate plugin $this->plugin_name.";
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
            new \ilComponentUpdatePluginObjective($this->plugin_name)
        ];
    }

    /**
     * @inheritdoc
     */
    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $ORIG_DIC = $this->initEnvironment($environment);

        $plugin = $GLOBALS["DIC"]["ilPluginAdmin"]->getRawPluginDataFor($this->plugin_name);

        if (!is_null($plugin) && $plugin['activation_possible'] && $plugin['supports_cli_setup']) {
            $pl = ilPlugin::getPluginObject(
                $plugin['component_type'],
                $plugin['component_name'],
                $plugin['slot_id'],
                $plugin['name']
            );

            $pl->activate();
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

        return $plugin['activation_possible'];
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
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class() {
            public function getLogger()
            {
            }
        };
        $GLOBALS["DIC"]["ilBench"] = null;
        $GLOBALS["DIC"]["lng"] = new ilLanguage('en');
        $GLOBALS["DIC"]["ilPluginAdmin"] = $plugin_admin;
        $GLOBALS["DIC"]["ilCtrl"] = new ilCtrl();
        $GLOBALS["DIC"]["ilias"] = null;
        $GLOBALS["DIC"]["ilLog"] = null;
        $GLOBALS["DIC"]["ilErr"] = null;
        $GLOBALS["DIC"]["tree"] = null;
        $GLOBALS["DIC"]["ilAppEventHandler"] = null;
        $GLOBALS["DIC"]["ilSetting"] = new ilSetting();
        $GLOBALS["DIC"]["objDefinition"] = new ilObjectDefinition();
        $GLOBALS["DIC"]["ilUser"] = new class() {
            public $prefs = [];
            public function __construct()
            {
                $this->prefs['language'] = 'en';
            }
        };

        if (!defined('DEBUG')) {
            define('DEBUG', false);
        }

        return $DIC;
    }
}
