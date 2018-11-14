<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use ilAccessHandler;
use ilAppEventHandler;
use ilConditionObjectAdapter;
use ilConditionService;
use ilCtrl;
use ilDBInterface;
use ilHelpGUI;
use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\GlobalScreen\Services;
use ilIniFile;
use ilLanguage;
use ilLearningHistoryService;
use ilNewsService;
use ilObjectService;
use ilObjUser;
use ilSetting;
use ilStyleDefinition;
use ilTabsGUI;
use ilToolbarGUI;
use ilTree;
use InvalidArgumentException;
use Pimple\Container as PimpleContainer;

/**
 * Class Container
 *
 * Customizing of pimple-DIC for ILIAS.
 *
 * This just exposes some of the services in the container as plain methods
 * to help IDEs when using ILIAS.
 *
 * @package ILIAS\DI
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @since   5.2
 */
class Container extends PimpleContainer {

	/**
	 * Container constructor
	 *
	 * @param array $values
	 */
	public function __construct(array $values = []) {
		parent::__construct($values);
	}


	/**
	 * Get interface to the Database.
	 *
	 * @return ilDBInterface
	 */
	public function database(): ilDBInterface {
		return $this["ilDB"];
	}


	/**
	 * Get interface to get interfaces to all things rbac.
	 *
	 * @return RBACServices
	 */
	public function rbac(): RBACServices {
		return new RBACServices($this);
	}


	/**
	 * Get the interface to the control structure.
	 *
	 * @return ilCtrl
	 */
	public function ctrl(): ilCtrl {
		return $this["ilCtrl"];
	}


	/**
	 * Get the current user.
	 *
	 * @return ilObjUser
	 */
	public function user(): ilObjUser {
		return $this["ilUser"];
	}


	/**
	 * Get interface for access checks.
	 *
	 * @return ilAccessHandler
	 */
	public function access(): ilAccessHandler {
		return $this["ilAccess"];
	}


	/**
	 * Get interface to the repository tree.
	 *
	 * @return ilTree
	 */
	public function repositoryTree(): ilTree {
		return $this["tree"];
	}


	/**
	 * Get interface to the i18n service.
	 *
	 * @return ilLanguage
	 */
	public function language(): ilLanguage {
		return $this["lng"];
	}


	/**
	 * Get interface to get interfaces to different loggers.
	 *
	 * @return LoggingServices
	 */
	public function logger(): LoggingServices {
		return new LoggingServices($this);
	}


	/**
	 * Get interface to the toolbar.
	 *
	 * @return ilToolbarGUI
	 */
	public function toolbar(): ilToolbarGUI {
		return $this["ilToolbar"];
	}


	/**
	 * Get interface to the tabs
	 *
	 * @return ilTabsGUI
	 */
	public function tabs(): ilTabsGUI {
		return $this["ilTabs"];
	}


	/**
	 * Get the interface to get services from UI framework.
	 *
	 * @return UIServices
	 */
	public function ui(): UIServices {
		return new UIServices($this);
	}


	/**
	 * Get the interface to the settings
	 *
	 * @return ilSetting
	 *
	 * @since 5.3
	 */
	public function settings(): ilSetting {
		return $this["ilSetting"];
	}


	/**
	 * Get the Filesystem service interface.
	 *
	 * @return Filesystems
	 *
	 * @since 5.3
	 */
	public function filesystem(): Filesystems {
		return $this['filesystem'];
	}


	/**
	 * Gets the file upload interface.
	 *
	 * @return FileUpload
	 *
	 * @since 5.3
	 */
	public function upload(): FileUpload {
		return $this['upload'];
	}


	/**
	 * @return BackgroundTaskServices
	 *
	 * @since 5.3
	 */
	public function backgroundTasks(): BackgroundTaskServices {
		return new BackgroundTaskServices($this);
	}


	/**
	 * @return HTTPServices
	 *
	 * @since 5.3
	 */
	public function http(): HTTPServices {
		return $this['http'];
	}


	/**
	 * @return ilAppEventHandler
	 *
	 * @since 5.3
	 */
	public function event(): ilAppEventHandler {
		return $this['ilAppEventHandler'];
	}


	/**
	 * @return ilIniFile
	 *
	 * @since 5.4
	 */
	public function iliasIni(): ilIniFile {
		return $this['ilIliasIniFile'];
	}


	/**
	 * @return ilIniFile
	 *
	 * @since 5.4
	 */
	public function clientIni(): ilIniFile {
		return $this['ilClientIniFile'];
	}


	/**
	 * @return ilStyleDefinition
	 *
	 * @since 5.4
	 */
	public function systemStyle(): ilStyleDefinition {
		return $this['styleDefinition'];
	}


	/**
	 * @return ilHelpGUI
	 *
	 * @since 5.4
	 */
	public function help(): ilHelpGUI {
		return $this['ilHelp'];
	}


	/**
	 * @return Services
	 *
	 * @since 5.4
	 */
	public function globalScreen(): Services {
		return new Services();
	}


	/**
	 * Get conditions service
	 *
	 * @return ilConditionService
	 *
	 * @since 5.4
	 */
	public function conditions(): ilConditionService {
		return ilConditionService::getInstance(new ilConditionObjectAdapter());
	}


	/**
	 * @return ilLearningHistoryService
	 *
	 * @since 5.4
	 */
	public function learningHistory(): ilLearningHistoryService {
		return new ilLearningHistoryService($this->user(), $this->language(), $this->ui(), $this->access(), $this->repositoryTree());
	}


	/**
	 * @return ilNewsService
	 *
	 * @since 5.4
	 */
	public function news(): ilNewsService {
		return new ilNewsService($this->language(), $this->settings(), $this->user());
	}


	/**
	 * @return ilObjectService
	 *
	 * @since 5.4
	 */
	public function object(): ilObjectService {
		return new ilObjectService($this->language(), $this->settings(), $this->filesystem(), $this->upload());
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
	 *
	 * @since 5.4
	 */
	public function isDependencyAvailable(string $name): bool {
		try {
			$this->{$name}();
		} catch (InvalidArgumentException $e) {
			return false;
		}

		return true;
	}
}
