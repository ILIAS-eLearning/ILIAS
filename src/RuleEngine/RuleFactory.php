<?php

namespace ILIAS\RuleEngine;

use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEngine\Action\Action;

class RuleFactory implements RuleFactoryInterface {

	/**
	 * @param Action $action
	 * @param Specification $specification
	 *
	 * @return Rule
	 */
	public function createRule(Action $action, Specification $specification) {

	}
}