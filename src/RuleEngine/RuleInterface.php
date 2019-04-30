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
interface RuleInterface {

	/**
	 * Returns a list of accessed variables.
	 *
	 * @return \Hoa\Ruler\Model\Bag\Context[]
	 */
	public function getAccesses(): array;

	/**
	 * Returns a list of used operators.
	 *
	 * @return \Hoa\Ruler\Model\Operator[]
	 */
	public function getOperators(): array;

	/**
	 * Returns a list of used parameters.
	 *
	 * @return \RulerZ\Model\Parameter[]
	 */
	public function getParameters(): array;

}