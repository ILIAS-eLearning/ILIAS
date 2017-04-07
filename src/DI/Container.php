<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

/**
 * Customizing of pimple-DIC for ILIAS.
 *
 * This just exposes some of the services in the container as plain methods
 * to help IDEs when using ILIAS.
 */
class Container extends \Pimple\Container {
	/**
	 * Get interface to the Database.
	 *
	 * @return	\ilDB
	 */
	public function database() {
		return $this["ilDB"];
	}

	/**
	 * Get interface to get interfaces to all things rbac.
	 *
	 * @return	RBACServices
	 */
	public function rbac() {
		return new RBACServices($this);
	}

	/**
	 * Get the interface to the control structure.
	 *
	 * @return	\ilCtrl
	 */
	public function ctrl() {
		return $this["ilCtrl"];
	}

	/**
	 * Get the current user.
	 *
	 * @return	\ilObjUser
	 */
	public function user() {
		return $this["ilUser"];
	}

	/**
	 * Get interface for access checks.
	 *
	 * @return	\ilAccessHandler
	 */
	public function access() {
		return $this["ilAccess"];
	}

	/**
	 * Get interface to the repository tree.
	 *
	 * @return	\ilTree
	 */
	public function repositoryTree() {
		return $this["tree"];
	}

	/**
	 * Get interface to the i18n service.
	 *
	 * @return	\ilLanguage
	 */
	public function language() {
		return $this["lng"];
	}

	/**
	 * Get interface to get interfaces to different loggers.
	 *
	 * @return	LoggingServices
	 */
	public function logger() {
		return new LoggingServices($this);
	}

	/**
	 * Get interface to the toolbar.
	 *
	 * @return	\ilLanguage
	 */
	public function toolbar() {
		return $this["ilToolbar"];
	}

	/**
	 * Get interface to the i18n service.
	 *
	 * @return	\ilLanguage
	 */
	public function tabs() {
		return $this["ilTabs"];
	}

	/**
	 * Get the interface to get services from UI framework.
	 *
	 * @return	UIServices
	 */
	public function ui() {
		return new UIServices($this);
	}
}
