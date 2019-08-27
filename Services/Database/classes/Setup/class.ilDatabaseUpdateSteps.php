<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Environment;


/**
 * This base-class simplifies the creation of (consecutive) database updates.
 *
 * Implement update steps on one or more tables by creating methods that follow
 * this schema:
 *
 * public function step_1(\ilDBInterface $db) { ... }
 *
 * The class will figure out which of them haven't been performed yet and need
 * to be executed.
 *
 * If the class takes care of only one table or a set of related tables it will
 * be easier to maintain.
 *
 * If for some reason you rely on update steps from other db-updated-classes
 * implement `getPreconditionSteps`.
 */
abstract class ilDatabaseUpdateSteps extends ilDatabaseObjective {
	const STEP_METHOD_PREFIX = "step_";

	final public function __construct(\ilDatabaseSetupConfig $config) {
		parent::__construct($config);
	}

	/**
	 * The hash for the objective is calculated over the classname and the steps
	 * that are contained.
	 */
	final public function getHash() : string {
		throw \LogicException("NYI!");
	}

	public function getLabel() : string {
		return "Database update steps in ".static::class;
	}

	/**
	 * @inheritdocs
	 */
	final public function isNotable() : bool {
		return true;
	}

	/**
	 * @inheritdocs
	 */
	final public function getPreconditions(Environment $environment) : array {
		if ($environment->getResource(Setup\Environment::RESOURCE_DATABASE)) {
		}
		return [
			new \ilDatabaseExistsObjective($this->config)
		];
	}

	/**
	 * @inheritdocs
	 */
	final public function achieve(Environment $environment) : Environment {
		$db = $environment->getResource(Environment::RESOURCE_DATABASE);

		foreach ($this->getSteps() as $num => $method) {
			call_user_func($method, $db);
		}

		return $environment;
	}

	/**
	 * Get the step functions in this class.
	 */
	final protected function getSteps() {
		$steps = [];

		foreach (get_class_methods(static::class) as $method) {
			if (stripos($method, self::STEP_METHOD_PREFIX) !== 0) {
				continue;
			}

			$number = substr($method, strlen(self::STEP_METHOD_PREFIX));

			if (!preg_match("/^[1-9]\d*$/", $number)) {
				throw new \LogicException("Method $method seems to be a step but has an odd looking number");
			}

			$steps[(int)$number] = [$this, $method];	
		}

		ksort($steps);

		return $steps;
	}
}
