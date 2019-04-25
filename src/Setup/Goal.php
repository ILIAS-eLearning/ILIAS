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
	 * Get a hash for this goal.
	 *
	 * The hash of two goals must be the same, if they are the same goal, with
	 * the same config on the same environment, i.e. if the one is achieved the
	 * other is achieved as well because they are the same.
	 */
	public function getHash() : string;

	/**
	 * Get a label that describes this goal.
	 */
	public function getLabel() : string;

	/**
	 * Get to know if this is an interesting goal for a human.
	 */
	public function isNotable() : bool;

	/**
	 * Goals may require resources to be reached.
	 *
	 * @throw \RuntimeException if the requested resource is not what the goal expected
	 */
	public function withResourcesFrom(Environment $environment) : Goal;

	/**
	 * Goals might depend on other goals.
	 *
	 * @throw UnachievableException if the goal is not achievable
	 *
	 * @return Goal[]
	 */
	public function getPreconditions() : array;

	/**
	 * Goals can be achieved. They might add resources to the environment when
	 * they have been achieved.
	 *
	 * @throw \LogicException if there are unfullfilled preconditions.
	 * @return void
	 */
	public function achieve(Environment $environment);
}
