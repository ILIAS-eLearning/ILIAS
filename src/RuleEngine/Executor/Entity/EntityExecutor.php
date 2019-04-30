<?php

namespace ILIAS\RuleEngine\Executor\Entity;

use ILIAS\RuleEngine\Entity\Entity;
use ILIAS\RuleEngine\Executor\Executor;
use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEnginge\Target\SqlTarget\SqlTarget;

class EntityExecutor implements Executor {

	/**
	 * @param Entity $target
	 * @param string $rule
	 * @param array  $operators
	 */
	public function filter($target, Specification $specification, array $operators) {
		global $DIC;

		$select = $select = $target->getQuery() . " WHERE " . implode(" AND ", $this->buildWhere($target, $specification));

		$res = $DIC->database()->query($select)->execute();

		return $DIC->database()->fetchAll($res);
	}


	public function satisfies($target, array $parameters, array $operators) {
		// TODO: Implement satisfies() method.
	}


	/**
	 * @param Entity        $target
	 * @param Specification $specification
	 */
	private function buildWhere(Entity $target, Specification $specification) {
		global $DIC;

		$where = [];

		if ($target->hasField($specification->getKey())) {
			//TODO operator handler

			$where[] = $DIC->database()
				->equals($specification->getKey(), $specification->getValue(), $target->getTypeFor($specification->getKey()), false);
		}

		return $where;
	}


	public function supports($target_compiler): bool {
		return $target_compiler instanceof SqlTarget;
	}
}

