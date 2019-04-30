<?php

namespace ILIAS\RuleEngine\Executor;

use ILIAS\RuleEngine\Context\ExecutionContext;

interface ExecutorInterface {

	public function filter($target, string $rule, array $operators);

	public function satisfies($target, array $parameters, array $operators);

	public function supports($target_compiler): bool;
}