<?php

namespace ILIAS\RuleEngine;

use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEngine\Action\Action;

interface RuleFactoryInterface {

	/**
	 * @param
	 * @param Specification $specification
	 *
	 * @return Rule
	 * @see ilAppEventListener()
	 *
	 */
	public function createRule(Action $action, Specification $specification);
}