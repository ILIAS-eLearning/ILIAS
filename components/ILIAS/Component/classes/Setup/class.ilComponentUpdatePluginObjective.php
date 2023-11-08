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

use ILIAS\Setup;
use ILIAS\DI;
use ILIAS\Setup\Objective\ClientIdReadObjective;
use ILIAS\components\Logging\NullLogger;

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
    public function getHash(): string
    {
        return hash("sha256", self::class . $this->plugin_name);
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return "Update plugin $this->plugin_name.";
    }

    /**
     * @inheritdoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPreconditions(Setup\Environment $environment): array
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
    public function achieve(Setup\Environment $environment): Setup\Environment
    {
        $component_repository = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY);
        $component_factory = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_FACTORY);
        $info = $component_repository->getPluginByName($this->plugin_name);

        if (!$info->supportsCLISetup()) {
            throw new \RuntimeException(
                "Plugin $this->plugin_name does not support command line setup."
            );
        }

        if (!$info->isUpdateRequired()) {
            throw new \RuntimeException(
                "Plugin $this->plugin_name is already updated."
            );
        }

        [$ORIG_DIC, $ORIG_ilDB] = $this->initEnvironment($environment, $component_repository, $component_factory);
        $plugin = $component_factory->getPlugin($info->getId());
        $plugin->update();
        $GLOBALS["DIC"] = $ORIG_DIC;
        $GLOBALS["ilDB"] = $ORIG_ilDB;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment): bool
    {
        $component_repository = $environment->getResource(Setup\Environment::RESOURCE_COMPONENT_REPOSITORY);
        $plugin = $component_repository->getPluginByName($this->plugin_name);

        return $plugin->isUpdateRequired();
    }

    protected function initEnvironment(
        Setup\Environment $environment,
        \ilComponentRepository $component_repository,
        \ilComponentFactory $component_factory
    ): array {
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
        $GLOBALS["DIC"]["component.repository"] = $component_repository;
        $GLOBALS["DIC"]["component.factory"] = $component_factory;
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["ilDB"] = $db;
        $GLOBALS["DIC"]["ilIliasIniFile"] = $ini;
        $GLOBALS["DIC"]["ilClientIniFile"] = $client_ini;
        $GLOBALS["DIC"]["ilLogger"] = new NullLogger();
        $GLOBALS["DIC"]["ilLog"] = new NullLogger();
        $GLOBALS["DIC"]["ilLoggerFactory"] = new class () extends ilLoggerFactory {
            public function __construct()
            {
            }
            public static function getRootLogger(): ilLogger
            {
                return $GLOBALS["DIC"]["ilLogger"];
            }
            public static function getLogger(string $a_component_id): ilLogger
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
        $GLOBALS["DIC"]["tree"] = new class () extends ilTree {
            public function __construct()
            {
            }
        };
        $GLOBALS["DIC"]["ilAppEventHandler"] = new class () extends ilAppEventHandler {
            public function __construct()
            {
            }
            public function raise($a_component, $a_event, $a_parameter = ""): void
            {
            }
        };
        $GLOBALS["DIC"]["ilObjDataCache"] = new ilObjectDataCache();
        $GLOBALS["DIC"]["ilSetting"] = new ilSetting();
        $GLOBALS["DIC"]["objDefinition"] = new ilObjectDefinition();
        $GLOBALS["DIC"]["rbacadmin"] = new class () extends ilRbacAdmin {
            public function __construct()
            {
            }
        };
        $GLOBALS["DIC"]["rbacreview"] = new class () extends ilRbacReview {
            public function __construct()
            {
            }
        };
        $GLOBALS["DIC"]["ilUser"] = new class () extends ilObjUser {
            public array $prefs = [];

            public function __construct()
            {
                $this->prefs["language"] = "en";
            }
        };

        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", dirname(__FILE__, 6));
        }

        if (!defined('SYSTEM_ROLE_ID')) {
            define('SYSTEM_ROLE_ID', '2');
        }

        if (!defined("CLIENT_ID")) {
            define('CLIENT_ID', $client_ini->readVariable('client', 'name'));
        }

        if (!defined("ILIAS_WEB_DIR")) {
            define('ILIAS_WEB_DIR', dirname(__DIR__, 5) . "/cli/data/");
        }

        return [$ORIG_DIC, $ORIG_ilDB];
    }
}
