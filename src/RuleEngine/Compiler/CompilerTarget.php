<?php

namespace ILIAS\RuleEnginge\Compiler;

interface CompilerTarget
{
	public const MODE_FILTER = 'filter';
	public const MODE_APPLY_FILTER = 'apply_filter';
	public const MODE_SATISFIES = 'satisfies';

	/**
	 * Compiles the given rule.
	 *
	 * @param Rule $rule The rule.
	 * @param Context $compilationContext The compilation context.
	 */
	public function compile(Rule $rule, Context $compilationContext): Executor;
	/**
	 * Indicates whether the given target is supported or not.
	 *
	 * @param mixed  $target The target to test.
	 * @param string $mode The execution mode (see MODE_* constants).
	 */
	public function supports($target, string $mode): bool;
	/**
	 * Returns a hint that will be used to make the rule identifying process more
	 * accurate.
	 *
	 * @param Context $context The compilation context.
	 *
	 * @return string The hint (empty string if not relevant).
	 */
	public function getRuleIdentifierHint(string $rule, Context $context): string;
	/**
	 * Define a runtime operator.
	 *
	 * @param callable $transformer The operator implementation (will be called at runtime when the operator is used).
	 */
	public function defineOperator(string $name, callable $transformer): void;
	/**
	 * Define a compile-time operator.
	 *
	 * @param callable $transformer The operator implementation (will be called at compile-time when the operator is used).
	 */
	public function defineInlineOperator(string $name, callable $transformer): void;
	public function getOperators(): array;
}