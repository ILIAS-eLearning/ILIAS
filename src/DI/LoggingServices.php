<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\DI;

use ilLog;
use ilLogger;
use ilLoggerFactory;

/**
 * Provides fluid interface to Logging services.
 *
 * @package ILIAS\DI
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
	 * Get interface to the global logger.
	 *
	 * @return ilLogger
	 */
	public function root() {
		return $this->loggerFactory()->getRootLogger();
	}


	/**
	 * @return ilLog
	 */
	public function log() {
		return $this->container["ilLog"];
	}


	/**
	 * @return ilLoggerFactory
	 */
	public function loggerFactory() {
		return $this->container["ilLoggerFactory"];
	}


	/**
	 * Get a component logger.
	 *
	 * @return ilLogger
	 */
	public function __call($method_name, $args) {
		assert(count($args) === 0);

		return $this->loggerFactory()->getLogger($method_name);
	}
}
