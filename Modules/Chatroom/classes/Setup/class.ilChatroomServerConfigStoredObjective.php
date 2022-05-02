<?php declare(strict_types=1);

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

use ILIAS\DI;
use ILIAS\Setup;

/**
 * Store information about https is enabled
 */
class ilChatroomServerConfigStoredObjective implements Setup\Objective
{
    protected ilChatroomSetupConfig $config;

    public function __construct(ilChatroomSetupConfig $config)
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
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilFileSystemComponentDataDirectoryCreatedObjective("chatroom")
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

        $chatAdministrations = ilObject::_getObjectsByType('chta');
        $chatAdministration = current($chatAdministrations);

        $chat_admin = new ilChatroomAdmin((int) $chatAdministration['obj_id']);
        $settings = $chat_admin->loadGeneralSettings();

        $settings['address'] = $this->config->getAddress();
        $settings['port'] = $this->config->getPort();
        $settings['sub_directory'] = $this->config->getSubDirectory();
        $settings['protocol'] = $this->config->getProtocol();
        $settings['cert'] = $this->config->getCert();
        $settings['key'] = $this->config->getKey();
        $settings['dhparam'] = $this->config->getDhparam();
        $settings['log'] = $this->config->getLog();
        $settings['log_level'] = $this->config->getLogLevel();
        $settings['error_log'] = $this->config->getErrorLog();
        $settings['ilias_proxy'] = (int) $this->config->hasIliasProxy();
        $settings['ilias_url'] = $this->config->getIliasUrl();
        $settings['client_proxy'] = (int) $this->config->hasClientProxy();
        $settings['client_url'] = $this->config->getClientUrl();
        $settings['deletion_mode'] = (int) $this->config->hasDeletionMode();
        $settings['deletion_unit'] = $this->config->getDeletionUnit();
        $settings['deletion_value'] = $this->config->getDeletionValue();
        $settings['deletion_time'] = $this->config->getDeletionTime();

        $chat_admin->saveGeneralSettings((object) $settings);

        if (!defined("CLIENT_DATA_DIR")) {
            define(
                "CLIENT_DATA_DIR",
                $filesystem_config->getDataDir() . "/" . ((string) $common_config->getClientId())
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
        return $this->config->getAddress() !== '' && $this->config->getPort() !== 0;
    }
}
