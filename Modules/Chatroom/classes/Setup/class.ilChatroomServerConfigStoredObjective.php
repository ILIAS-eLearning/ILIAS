<?php declare(strict_types=1);

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\DI;

/**
 * Store information about https is enabled
 */
class ilChatroomServerConfigStoredObjective implements Setup\Objective
{
    /**
     * @var	\ilChatroomSetupConfig
     */
    protected $config;

    public function __construct(\ilChatroomSetupConfig $config)
    {
        $this->config = $config;
    }

    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "Store information about chatroom server to db";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        $common_config = $environment->getConfigFor("common");
        $db_config = $environment->getConfigFor("database");
        return [
            new \ilIniFilesPopulatedObjective($common_config),
            new \ilDatabasePopulatedObjective($db_config),
            new \ilDatabaseUpdatedObjective($db_config)
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $common_config = $environment->getConfigFor("common");
        $filesystem_config = $environment->getConfigFor("filesystem");

        // ATTENTION: This is a total abomination. It only exists to allow various
        // sub components of the various readers to run. This is a memento to the
        // fact, that dependency injection is something we want. Currently, every
        // component could just service locate the whole world via the global $DIC.
        $DIC = $GLOBALS["DIC"];
        $GLOBALS["DIC"] = new DI\Container();
        $GLOBALS["DIC"]["ilDB"] = $db;
        $GLOBALS["DIC"]["ilBench"] = null;

        $chat_admin = ilChatroomAdmin::getDefaultConfiguration();
        $settings = $chat_admin->loadGeneralSettings();

        $settings['address'] = $this->config->getAddress();
        $settings['port'] = $this->config->getPort();
        $settings['sub_directory'] = $this->config->getSubDirectory();
        $settings['protocol'] = $this->config->getProtocol();
        $settings['cert'] = $this->config->getCert();
        $settings['key'] = $this->config->getKey();
        $settings['dhparam'] = $this->config->getDhparam();
        $settings['log'] = (int)$this->config->getLog();
        $settings['log_level'] = $this->config->getLogLevel();
        $settings['error_log'] = $this->config->getErrorLog();
        $settings['ilias_proxy'] = (int)$this->config->hasIliasProxy();
        $settings['ilias_url'] = $this->config->getIliasUrl();
        $settings['client_proxy'] = (int)$this->config->hasClientProxy();
        $settings['client_url'] = $this->config->getClientUrl();
        $settings['deletion_mode'] = (int)$this->config->hasDeletionMode();
        $settings['deletion_unit'] = $this->config->getDeletionUnit();
        $settings['deletion_value'] = $this->config->getDeletionValue();
        $settings['deletion_time'] = $this->config->getDeletionTime();

        $chat_admin->saveGeneralSettings((object) $settings);

        if (!defined("CLIENT_DATA_DIR")) {
            define(
                "CLIENT_DATA_DIR",
                $filesystem_config->getDataDir() . "/" . $common_config->getClientId()
            );
        }

        $fileHandler = new ilChatroomConfigFileHandler();
        $fileHandler->createServerConfigFile($settings);

        $GLOBALS["DIC"] = $DIC;

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return $this->config->getAddress() != '' && $this->config->getPort() != 0;
    }
}
