<?php

namespace ILIAS\RuleEngine\Context;

use ILIAS\RuleEngine\Specification\Specification;

/**
 * Context
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
abstract class AbstractContext {

	/**
	 * @var Specification[]|void
	 */
	protected $base_filter = null;


	/**
	 * @return string
	 */
	abstract function returnDbTableName():string;


	/**
	 * @param Specification[] $specification
	 *
	 * @return array
	 */
	public function findSatisfying(array $specifications):array {

		foreach ($specifications as $specification) {
			/**
			 * @var Specification $specification
			 */ //$specification->getParameters();
			//TODO
		}
	}


	/**
	 * @return Specification[]|void
	 */
	public function getBaseFilter() {
		return $this->base_filter;
	}
}