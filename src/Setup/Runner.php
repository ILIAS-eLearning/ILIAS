<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * Tries to achieve a goal. 
 */
class Runner {
	/**
	 * @var	Environment
	 */
	protected $environment;

	/**
	 * @var ConfigurationLoader
	 */
	protected $configuration_loader;

	/**
	 * @var Goal
	 */
	protected $goal;

	public function __construct(Environment $environment, ConfigurationLoader $configuration_loader, Goal $goal) {
		$this->environment = $environment;
		$this->configuration_loader = $configuration_loader;
		$this->goal = $goal;
	}

	public function run() {
		$type = $this->goal->getType();
		$config = $this->configuration_loader->loadConfigurationFor($type);
		$goal = $this->goal
			->withResourcesFrom($this->environment)
			->withConfiguration($config);
		$preconditions = $this->goal->getPreconditions();
		$goal->achieve($this->environment);
	}

	/**
	 * @return \Traversable<Goal>
	 */
	public function allGoals() : \Traversable {
		$stack = [$this->goal];

		while(count($stack) > 0) {
			$cur = $this->initGoal(
				array_pop($stack)
			);

			$preconditions = $cur->getPreconditions();

			if (count($preconditions) === 0) {
				yield $cur;
			}
			else {
				array_push(
					$stack,
					$cur,
					...array_reverse($preconditions)
				);
			}
		}
	}

	protected function initGoal(Goal $goal) : Goal {
		$type = $this->goal->getType();
		$config = $this->configuration_loader->loadConfigurationFor($type);
		return $goal
			->withConfiguration($config)
			->withResourcesFrom($this->environment);
	}
}
