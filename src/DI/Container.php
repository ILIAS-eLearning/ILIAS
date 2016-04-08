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
	 * @return	ilDB
	 */
	public function db() {
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
	 * @return	ilCtrl
	 */
	public function ctrl() {
		
	}

	/**
	 * Get the current user.
	 *
	 * @return	ilUser
	 */
	public function user() {
		
	}

	/**
	 * Get interface for access checks.
	 *
	 * @return	ilAccessHandler
	 */
	public function access() {
		
	}

	/**
	 * Get interface to the repository tree.
	 *
	 * @return	ilTree
	 */
	public function tree() {
		
	}

	/**
	 * Get interface to the i18n service.
	 *
	 * @return	ilLanguage
	 */
	public function language() {
		
	}

	/**
	 * Get interface to get interfaces to different loggers.
	 *
	 * @return	LoggingServices
	 */
	public function logger() {
		return new LoggingServices();
	}
}