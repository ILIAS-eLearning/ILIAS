<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup\Environment;
use ILIAS\Setup\CallableObjective;
use ILIAS\Setup\NullObjective;
use ILIAS\Setup\Objective;


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

	/**
	 * @var	string[]|null
	 */
	protected $steps = null;

	/**
	 * @var	Objective
	 */
	protected $base;

	/**
	 * @param \ilObjective $base for the update steps, i.e. the objective that should
	 *                           have been reached before the steps of this class can
	 *                           even begin. Most propably this should be
	 *                           \ilDatabasePopulatedObjective.
	 */
	public function __construct(
		\ilDatabaseSetupConfig $config,
		Objective $base
	) {
		parent::__construct($config);
		$this->base = $base;
	}

	/**
	 * The hash for the objective is calculated over the classname and the steps
	 * that are contained.
	 */
	final public function getHash() : string {
		return hash(
			"sha256",
			get_class($this)
		);
	}

	public function getLabel() : string {
		return "Database update steps in ".get_class($this);
	}

	/**
	 * @inheritdocs
	 */
	public function isNotable() : bool {
		return true;
	}

	/**
	 * @inheritdocs
	 */
	public function getPreconditions(Environment $environment) : array {
		$steps = $this->getSteps();
		return [$this->getStep(array_pop($steps))];
	}

	/**
	 * @inheritdocs
	 */
	public function achieve(Environment $environment) : Environment {
		return $environment;
	}

	/**
	 * Get a database update step.
	 *
	 * @throws \LogicException if step is unknown
	 */
	public function getStep(string $name) : ilDatabaseUpdateStep {
		return new ilDatabaseUpdateStep(
			$this,
			$name,
			...$this->getPreconditionsOfStep($name)
		);
	}

	/**
	 * @return Objective[]
	 */
	protected function getPreconditionsOfStep(string $name) : array {
		$others = $this->getStepsBefore($name);
		if (count($others) === 0) {
			return [$this->base];
		}
		return [$this->getStep(array_pop($others))];
	}

	/**
	 * Get the step-methods in this class.
	 *
	 * @return string[]
	 */
	public function getSteps() : array {
		if (!is_null($this->steps)) {
			return $this->steps;
		}

		$this->steps = [];

		foreach (get_class_methods(static::class) as $method) {
			if (stripos($method, self::STEP_METHOD_PREFIX) !== 0) {
				continue;
			}

			$number = substr($method, strlen(self::STEP_METHOD_PREFIX));

			if (!preg_match("/^[1-9]\d*$/", $number)) {
				throw new \LogicException("Method $method seems to be a step but has an odd looking number");
			}

			$this->steps[$method] = $method;
		}

		asort($this->steps);

		return $this->steps;
	}

	/**
	 * Get the step-methods before the given step.
	 *
	 * ATTENTION: The steps are sorted in ascending order.
	 *
	 * @throws \LogicException if step is not known
	 * @return string[]
	 */
	public function getStepsBefore(string $other) {
		$this->getSteps();
		if (!isset($this->steps[$other])) {
			throw new \LogicException("Unknown database update step: $other");
		}

		$res = [];
		foreach ($this->steps as $method) {
			if ($method	=== $other) {
				break;
			}
			$res[$method] = $method;
		}
		return $res;
	}
}
