<?php

namespace ILIAS\RuleEngine\Executor\Entity;

use ILIAS\RuleEngine\Entity\Entity;
use ILIAS\RuleEngine\Executor\Executor;
use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEnginge\Target\ArrayVisitor\ArrayTarget;
use ILIAS\RuleEngine\Result\IteratorTools;

class ArrayExecutor implements Executor {

	/**
	 * @param               $target
	 * @param Specification $specification
	 * @param array         $operators
	 *
	 * @return \Iterator
	 */
	public function filter($target, Specification $specification, array $operators) {

		return IteratorTools::fromGenerator(function () use ($target, $specification, $operators) {
			foreach ($target as $row) {
				yield $row;
			}
		});
	}


	public function satisfies($target, array $parameters, array $operators) {
		// TODO: Implement satisfies() method.
	}


	public function supports($target_compiler): bool {
		return $target_compiler instanceof ArrayTarget;
	}
}

