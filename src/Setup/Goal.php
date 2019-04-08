<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI;

/**
 * A goal is a desired state of the system that is supposed to be created
 * by the setup.
 *
 * This interface would benefit from generics, in fact it would be parametrized
 * with a Config-type.
 */
interface Goal {
	/**
	 * Get a label that describes this goal.
	 */
	public function getLabel() : string;

	/**
	 * Get to know if this is an interesting goal for a human.
	 */
	public function isNotable() : string;

	/**
	 * Goals can be configurable.
	 *
	 * @throw \InvalidArgumentException if Config does not match the Goal.
	 */
	public function withConfiguration(Config $config) : Goal;

	/**
	 * Goals may provide a default configuration.
	 *
	 * @return Config|null
	 */
	public function getDefaultConfiguration();

	/**
	 * Goals may require resources to be reached.
	 *
	 * @throw \RuntimeException if the requested resource is not what the goal expected
	 */
	public function withResourcesFrom(Environment $environment);

	/**
	 * Goals may provide an input to configure them, which could be displaying
	 * some preset configuration.
	 *
	 * @throw InvalidArgumentException if Config does not match the Goal
	 * @return UI\Component\Input\Field\Input
	 */
	public function getConfigurationInput(Config $config = null);

	/**
	 * Goals might depend on other goals.
	 *
	 * @throw UnachievableException if the goal is not achievable
	 *
	 * @return Goal[]
	 */
	public function getPreconditions();

	/**
	 * Goals can be achieved. They might add resources to the environment when
	 * they have been achieved.
	 *
	 * @throw \LogicException if there are unfullfilled preconditions.
	 */
	public function achieve(Environment $environment);
}
