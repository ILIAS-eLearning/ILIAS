<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

/**
 * Provides fluid interface to RBAC services.
 */
class LoggingServices {
	/**
	 * Get interface to the global logger.
	 *
	 * @return	ilLogger
	 */
	public function root() {
		
	}

	/**
	 * Get a component logger.
	 *
	 * @return	ilLogger
	 */
	public function __call($method_name, $args) {
		assert('count($args) === 0');
		
	}
}