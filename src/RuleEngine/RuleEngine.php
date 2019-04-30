<?php

namespace ILIAS\RuleEngine;

use ILIAS\RuleEngine\Executor\ExecutorInterface;
use ILIAS\RuleEnginge\Compiler\Compiler;
use ILIAS\RuleEngine\Context\ExecutionContext;

use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEnginge\Compiler\CompilerTarget;

class RuleEngine {
	/**
	 * @var Compiler
	 */
	private $compiler;
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
	public function __construct(array $compilerTargets, array $executors)
	{

		foreach ($compilerTargets as $targetCompiler) {
			$this->registerCompilerTargets($targetCompiler);
		}

		foreach ($executors as $executor) {
			$this->registerExecutors($executor);
		}
	}

	/**
	 * Registers a new target compiler.
	 *
	 * @param CompilerTarget $compilerTarget The target compiler to register.
	 */
	public function registerCompilerTargets(CompilerTarget $compiler_target): void
	{
		$this->compiler_targets[] = $compiler_target;
	}

	/**
	 * Registers a new target compiler.
	 *
	 * @param CompilerTarget $compilerTarget The target compiler to register.
	 */
	public function registerExecutors(ExecutorInterface $executor): void
	{
		$this->executors[] = $executor;
	}

	/**
	 * Filters a target using the given rule and parameters.
	 * The target compiler to use is determined at runtime using the registered ones.
	 *
	 * @param mixed  $target           The target to filter.
	 * @param string $rule             The rule to apply.
	 * @param array  $parameters       The parameters used in the rule.
	 *
	 * @return \Traversable The filtered target.
	 *
	 * @throws TargetUnsupportedException
	 */
	public function filter($target, string $rule, array $parameters = []) {

		$target_compiler = $this->findTargetCompiler($target, CompilerTarget::MODE_FILTER);

		$executor = $this->findExecutor($target_compiler);

		return $executor->filter($target, $parameters, $target_compiler->getOperators());
	}


	/**
	 * Filters a target using the given specification.
	 * The targetCompiler to use is determined at runtime using the registered ones.
	 *
	 * @param mixed         $target           The target to filter.
	 * @param Specification $specification    The specification to apply.
	 *
	 * @return mixed The filtered target.
	 *
	 */
	public function filterSpec($target, Specification $specification) {
		return $this->filter($target, $specification->getRule(), $specification->getParameters());
	}


	/**
	 * Apply the filters on a target using the given specification.
	 * The targetCompiler to use is determined at runtime using the registered ones.
	 *
	 * @param mixed         $target           The target to filter.
	 * @param Specification $spec             The specification to apply.
	 * @param array         $executionContext The execution context.
	 *
	 * @return mixed
	 *
	 * @throws TargetUnsupportedException
	 */
	public function applyFilterSpec($target, Specification $spec, array $executionContext = []) {
		return $this->applyFilter($target, $spec->getRule(), $spec->getParameters(), $executionContext);
	}


	/**
	 * Finds a target compiler supporting the given target.
	 *
	 * @param mixed  $target The target to filter.
	 * @param string $mode   The execution mode (MODE_FILTER or MODE_SATISFIES).
	 *

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
	 * Finds a target compiler supporting the given target.
	 *
	 * @param mixed  $target The target to filter.
	 * @param string $mode   The execution mode (MODE_FILTER or MODE_SATISFIES).
	 *

	 */
	private function findExecutor($target_compiler): ExecutorInterface {
		/** @var ExecutorInterface $executor */
		foreach ($this->executors as $executor) {
			if ($executor->supports($target_compiler)) {
				return $executor;
			}
		}

		//TODO throw no executor found
	}
}