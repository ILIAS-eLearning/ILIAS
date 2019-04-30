<?php

namespace  ILIAS\RuleEngine\Executor\Entity;

use ILIAS\RuleEngine\Entity\Entity;
use ILIAS\RuleEngine\Executor\ExecutorInterface;
use ILIAS\RuleEnginge\Target\ilSqlVisitor;

class EntityExecutor implements ExecutorInterface {

	/**
	 * @param Entity           $target
	 * @param array            $parameters
	 * @param array            $operators
	 * @param ExecutionContext $context
	 */
	public function filter($target, array $parameters, array $operators) {
		global $DIC;

		$select =
		$select = $target->getQuery()." WHERE ".implode(" AND ", $this->buildWhere($target,$parameters));

		$res = $DIC->database()->query($select)->execute();

		$arr_data = [];
		return $DIC->database()->fetchAll($res);
	}


	public function satisfies($target, array $parameters, array $operators) {
		// TODO: Implement satisfies() method.
	}


	/**
	 * @param Entity $target
	 * @param array $parameters
	 */
	private function buildWhere(Entity $target, array $parameters) {
		global $DIC;

		$where = [];
		foreach ($parameters as $key => $value) {
			if($target->hasField($key)) {
				//TODO other operators

				$where[] = $DIC->database()->equals($key, $value, $target->getTypeFor($key),false);
			}
		}

		return $where;
	}

	public function supports($target_compiler): bool {
		return $target_compiler instanceof ilSqlVisitor;
	}
}

