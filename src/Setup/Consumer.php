<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI;

/**
 * A consumer is some component that takes part in the setup process.
 */
interface Consumer {
	/**
	 * Does this consumer require a configuration?
	 */
	public function hasConfig() : bool;

	/**
	 * Consumers must provide an input to set the configuration if they have a
	 * configuration.
	 *
	 * @throw InvalidArgumentException if Config does not match the Consumer.. 
	 * @throw LogicException if Consumer has no Config
	 */
	public function getConfigInput(Config $config = null) : UI\Component\Input\Field\Input;

	/**
	 * Consumers must be able to create a configuration from a nested array.
	 *
	 * @throw InvalidArgumentException if array does not match the Consumer 
	 * @throw LogicException if Consumer has no Config
	 */
	public function getConfigFromArray(array $data) : Config; 

	/**
	 * Get the goals the consumer wants to achieve on setup.
	 *
	 * @throw InvalidArgumentException if Config does not match the Consumer.. 
	 */
	public function getInstallGoal(Config $config = null) : Goal;

	/**
	 * Get the goal the consumer wants to achieve on update.
	 *
	 * @throw InvalidArgumentException if Config does not match the Consumer.. 
	 */
	public function getUpdateGoal(Config $config = null) : Goal;
}
