<?php

namespace  ILIAS\RuleEngine\Executor\Entity;

use ILIAS\RuleEngine\Entity\Entity;
use ILIAS\RuleEngine\Executor\ExecutorInterface;
use ILIAS\RuleEnginge\Target\ArrayVisitor\ArrayVisitor;
use ILIAS\RuleEngine\Result\IteratorTools;

class ArrayExecutor implements ExecutorInterface {

	/**
	 * @param Entity           $target
	 * @param array            $parameters
	 * @param array            $operators
	 */
	public function filter($target, array $parameters, array $operators) {

		return IteratorTools::fromGenerator(function () use ($target, $parameters, $operators) {
			foreach ($target as $row) {
					yield $row;
			}
		});


	}


	public function satisfies($target, array $parameters, array $operators) {
		// TODO: Implement satisfies() method.
	}


	public function supports($target_compiler): bool {
		return $target_compiler instanceof ArrayVisitor;
	}
}

