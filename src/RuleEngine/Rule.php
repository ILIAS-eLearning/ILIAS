<?php

namespace ILIAS\RuleEngine;

use ILIAS\RuleEngine\Action\Action;
use ILIAS\RuleEngine\Context\Context;
use ILIAS\RuleEngine\Specification\Specification;

/**
 * Interface Rule
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
interface Rule {

	/**
	 * Events triggered if the evaluation of the rule is true
	 *
	 * @return Action
	 */
	public function Action(): Action;


	/**
	 * @return Specification
	 */
	public function getSpecification(): Specification;


	/**
	 * @param Context $context
	 */
	public function isSatisfiedBy(Context $context): bool;
}