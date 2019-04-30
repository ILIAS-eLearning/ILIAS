<?php

namespace ILIAS\RuleEngine;

use ILIAS\RuleEngine\Executor\ExecutorInterface;

use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEngine\Compiler\CompilerTarget;

class RuleEngine {

	/**
	 * @var compiler_targets[]
	 */
	private $compiler_targets = [];
	/**
	 * @var executors[]
	 */
	private $executors = [];


	/**
	 * @param array compilerTargets A list of compilation targets, each one handles a specific target type (an array, a DoctrineQueryBuilder, ...)
	 */
	public function __construct(array $compiler_targets, array $executors) {

		foreach ($compiler_targets as $compiler_target) {
			$this->registerCompilerTargets($compiler_target);
		}

		foreach ($executors as $executor) {
			$this->registerExecutors($executor);
		}
	}


	/**
	 * @param CompilerTarget $compiler_target
	 */
	public function registerCompilerTargets(CompilerTarget $compiler_target) {
		$this->compiler_targets[] = $compiler_target;
	}


	/**
	 * Registers a new target compiler.
	 *
	 * @param CompilerTarget $compilerTarget The target compiler to register.
	 */
	public function registerExecutors(ExecutorInterface $executor) {
		$this->executors[] = $executor;
	}


	/**
	 * @param        $target
	 * @param string $rule
	 * @param array  $parameters
	 *
	 * @return mixed
	 */
	public function filter($target, string $rule, array $parameters = []) {

		$target_compiler = $this->findTargetCompiler($target, CompilerTarget::MODE_FILTER);

		$executor = $this->findExecutor($target_compiler);

		return $executor->filter($target, $rule, $target_compiler->getOperators());
	}


	/**
	 * @param               $target
	 * @param Specification $specification
	 *
	 * @return mixed
	 */
	public function filterSpec($target, Specification $specification) {
		return $this->filter($target, $specification->getRule(), $specification->getParameters());
	}


	/**
	 * @param $target
	 * @param $mode
	 *
	 * @return CompilerTarget
	 */
	private function findTargetCompiler($target, $mode): CompilerTarget {
		/** @var CompilerTarget $target_compiler */
		foreach ($this->compiler_targets as $target_compiler) {
			if ($target_compiler->supports($target, $mode)) {
				return $target_compiler;
			}
		}
		//TODO throw no compiler found
	}


	/**
	 * @param $target_compiler
	 *
	 * @return ExecutorInterface|executors
	 */
	private function findExecutor($target_compiler) /*: ExecutorInterface */ {
		/** @var ExecutorInterface $executor */
		foreach ($this->executors as $executor) {
			if ($executor->supports($target_compiler)) {
				return $executor;
			}
		}
		//TODO throw no executor found
	}
}