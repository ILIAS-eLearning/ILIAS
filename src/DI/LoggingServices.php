<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use ilLogger;
use ilLoggerFactory;

/**
 * Provides fluid interface to Logging services.
 *
 * @package ILIAS\DI
 *
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 *
 * @since   5.3
 */
class LoggingServices {

	/**
	 * @var Container
	 */
	protected $container;


	/**
	 * LoggingServices constructor
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}


	/**
	 * @return ilLoggerFactory
	 */
	public function loggerFactory(): ilLoggerFactory {
		return $this->container["ilLoggerFactory"];
	}


	/**
	 * Get interface to the global logger.
	 *
	 * @return ilLogger
	 */
	public function root(): ilLogger {
		return $this->loggerFactory()->getRootLogger();
	}


	/**
	 * Get a component logger.
	 *
	 * @param string $method_name
	 * @param array  $args
	 *
	 * @return ilLogger
	 */
	public function __call(string $method_name, array $args): ilLogger {
		//assert(count($args) === 0);

		return $this->loggerFactory()->getLogger($method_name);
	}
}
