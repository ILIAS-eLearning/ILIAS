<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use Collator;
use ilAccessHandler;
use ilAppEventHandler;
use ilAuthSession;
use ilBrowser;
use ilCtrl;
use ilCtrlStructureReader;
use ilDBInterface;
use ilErrorHandling;
use ilHelpGUI;
use ILIAS;
use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ilIniFile;
use ilLanguage;
use ilLocatorGUI;
use ilMailMimeSenderFactory;
use ilMailMimeTransportFactory;
use ilMainMenuGUI;
use ilNavigationHistory;
use ilObjectDataCache;
use ilObjectDefinition;
use ilObjUser;
use ilPluginAdmin;
use ilSetting;
use ilStyleDefinition;
use ilTabsGUI;
use ilToolbarGUI;
use ilTree;
use InvalidArgumentException;
use Pimple\Container as PimpleContainer;
use Session;

/**
 * Class Container
 *
 * Customizing of pimple-DIC for ILIAS.
 *
 * This just exposes some of the services in the container as plain methods
 * to help IDEs when using ILIAS.
 *
 * @package ILIAS\DI
 */
class Container extends PimpleContainer {

	/**
	 * Get interface to the Database.
	 *
	 * @return ilDBInterface
	 */
	public function database() {
		return $this["ilDB"];
	}


	/**
	 * Get interface to get interfaces to all things rbac.
	 *
	 * @return RBACServices
	 */
	public function rbac() {
		return new RBACServices($this);
	}


	/**
	 * Get the interface to the control structure.
	 *
	 * @return ilCtrl
	 */
	public function ctrl() {
		return $this["ilCtrl"];
	}


	/**
	 * Get the current user.
	 *
	 * @return ilObjUser
	 */
	public function user() {
		return $this["ilUser"];
	}


	/**
	 * Get interface for access checks.
	 *
	 * @return ilAccessHandler
	 */
	public function access() {
		return $this["ilAccess"];
	}


	/**
	 * Get interface to the repository tree.
	 *
	 * @return ilTree
	 */
	public function repositoryTree() {
		return $this["tree"];
	}


	/**
	 * Get interface to the i18n service.
	 *
	 * @return ilLanguage
	 */
	public function language() {
		return $this["lng"];
	}


	/**
	 * Get interface to get interfaces to different loggers.
	 *
	 * @return LoggingServices
	 */
	public function logger() {
		return new LoggingServices($this);
	}


	/**
	 * Get interface to the toolbar.
	 *
	 * @return ilToolbarGUI
	 */
	public function toolbar() {
		return $this["ilToolbar"];
	}


	/**
	 * Get interface to the tabs
	 *
	 * @return ilTabsGUI
	 */
	public function tabs() {
		return $this["ilTabs"];
	}


	/**
	 * Get the interface to get services from UI framework.
	 *
	 * @return UIServices
	 */
	public function ui() {
		return new UIServices($this);
	}


	/**
	 * Get the interface to the settings
	 *
	 * @return ilSetting
	 */
	public function settings() {
		return $this["ilSetting"];
	}


	/**
	 * Get the Filesystem service interface.
	 *
	 * @return Filesystems
	 *
	 * @since  5.3
	 */
	public function filesystem() {
		return $this['filesystem'];
	}


	/**
	 * Gets the file upload interface.
	 *
	 * @return FileUpload
	 *
	 * @since  5.3
	 */
	public function upload() {
		return $this['upload'];
	}


	/**
	 * @return BackgroundTaskServices
	 *
	 * @since  5.3
	 */
	public function backgroundTasks() {
		return new BackgroundTaskServices($this);
	}


	/**
	 * @return HTTPServices
	 *
	 * @since  5.3
	 */
	public function http() {
		return $this['http'];
	}


	/**
	 * @return ilAppEventHandler
	 */
	public function event() {
		return $this['ilAppEventHandler'];
	}


	/**
	 * @return ilIniFile
	 */
	public function iliasIni() {
		return $this['ilIliasIniFile'];
	}


	/**
	 * @return ilIniFile
	 */
	public function clientIni() {
		return $this['ilClientIniFile'];
	}


	/**
	 * @return ilStyleDefinition
	 */
	public function systemStyle() {
		return $this['styleDefinition'];
	}


	/**
	 * @return ilHelpGUI
	 */
	public function help() {
		return $this['ilHelp'];
	}


	/**
	 * @return ilAuthSession
	 */
	public function authSession() {
		return $this["ilAuthSession"];
	}


	/**
	 * return ilBenchmark
	 */
	public function benchmark() {
		return $this["ilBench"];
	}


	/**
	 * @return ilBrowser
	 */
	public function browser() {
		return $this["ilBrowser"];
	}


	/**
	 * @return Collator
	 */
	public function collator() {
		return $this["ilCollator"];
	}


	/**
	 * @return ilCtrlStructureReader
	 */
	public function ctrlStructureReader() {
		return $this["ilCtrlStructureReader"];
	}


	/**
	 * @return ilErrorHandling
	 */
	public function error() {
		return $this["ilErr"];
	}


	/**
	 * @return ilNavigationHistory
	 */
	public function history() {
		return $this["ilNavigationHistory"];
	}


	/**
	 * @return ILIAS
	 */
	public function ilias() {
		return $this["ilias"];
	}


	/**
	 * @return ilLocatorGUI
	 */
	public function locator() {
		return $this["ilLocator"];
	}


	/**
	 * @return ilMailMimeSenderFactory
	 *
	 * @since  5.3
	 */
	public function mailMimeSenderFactory() {
		return $this["mail.mime.sender.factory"];
	}


	/**
	 * @return ilMailMimeTransportFactory
	 *
	 * @since  5.3
	 */
	public function mailMimeTransportFactory() {
		return $this["mail.mime.transport.factory"];
	}


	/**
	 * @return ilMainMenuGUI
	 */
	public function mainMenu() {
		return $this["ilMainMenu"];
	}


	/**
	 * @return ilObjectDataCache
	 */
	public function objDataCache() {
		return $this["ilObjDataCache"];
	}


	/**
	 * @return ilObjectDefinition
	 */
	public function objDefinition() {
		return $this["objDefinition"];
	}


	/**
	 * @return ilPluginAdmin
	 */
	public function pluginAdmin() {
		return $this["ilPluginAdmin"];
	}


	/**
	 * @return Session
	 */
	public function session() {
		return $this["sess"];
	}


	/**
	 * Note: Only use isDependencyAvailable if strictly required. The need for this,
	 * mostly points to some underlying problem needing to be solved instead of using this.
	 * This was introduced as temporary workaround. See: https://github.com/ILIAS-eLearning/ILIAS/pull/1064
	 *
	 * This is syntactic sugar for executing the try catch statement in the clients code.
	 * Note that the use of the offsetSet code of the default container should be avoided,
	 * since knowledge about the containers internal mechanism is injected.
	 *
	 * Example:
	 * //This is bad since the client should not need to know about the id's name
	 * $DIC->offsetSet("styleDefinition")
	 *
	 * //This is better, since the client just needs to know the name defined in the
	 * //interface of the component
	 * $DIC->isDependencyAvailable("systemStyle")
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isDependencyAvailable($name) {
		try {
			$this->$name();
		} catch (InvalidArgumentException $e) {
			return false;
		}

		return true;
	}
}
