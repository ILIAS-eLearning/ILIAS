<?php

namespace ILIAS\RuleEngine\Executor;

use ILIAS\RuleEngine\Specification\Specification;

interface Executor {

	public function filter($target, Specification $specification, array $operators);


	public function satisfies($target, array $parameters, array $operators);


	public function supports($target_compiler): bool;
}