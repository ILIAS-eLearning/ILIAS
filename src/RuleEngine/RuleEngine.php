<?php

namespace ILIAS\RuleEngine;

use ILIAS\RuleEngine\Executor\Executor;

use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEngine\Target\Target;

class RuleEngine {

	/**
	 * @var Target[]
	 */
	private $targets = [];
	/**
	 * @var Executor[]
	 */
	private $executors = [];


	/**
	 * @param array compilerTargets A list of compilation targets, each one handles a specific target type (an array, a DoctrineQueryBuilder, ...)
	 */
	public function __construct(array $targets, array $executors) {

		foreach ($targets as $target) {
			$this->registerTargets($target);
		}

		foreach ($executors as $executor) {
			$this->registerExecutors($executor);
		}
	}


	/**
	 * @param Target $compiler_target
	 */
	public function registerTargets(Target $compiler_target) {
		$this->targets[] = $compiler_target;
	}


	/**
	 * Registers a new target compiler.
	 *
	 * @param Target $compilerTarget The target compiler to register.
	 */
	public function registerExecutors(Executor $executor) {
		$this->executors[] = $executor;
	}


	/**
	 * @param               $target
	 * @param Specification $specification
	 *
	 * @return mixed
	 */
	public function filterSpec($target, Specification $specification) {
		$target_compiler = $this->findTarget($target, Target::MODE_FILTER);

		$executor = $this->findExecutor($target_compiler);

		return $executor->filter($target, $specification, $target_compiler->getOperators());
	}


	/**
	 * @param Target $target_value
	 * @param $mode
	 *
	 * @return Target
	 */
	private function findTarget($target_value, $mode) /*: Target */ {
		/** @var Target $target */
		foreach ($this->targets as $target) {
			if ($target->supports($target_value, $mode)) {
				return $target;
			}
		}
		//TODO throw no target found
	}


	/**
	 * @param $target
	 *
	 * @return Executor|executors
	 */
	private function findExecutor($target) /*: ExecutorInterface */ {
		/** @var Executor $executor
		 */
		foreach ($this->executors as $executor) {
			if ($executor->supports($target)) {
				return $executor;
			}
		}
		//TODO throw no executor found
	}
}