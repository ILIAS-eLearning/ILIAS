<?php

namespace ILIAS\RuleEngine\Context;

use ActiveRecord;
use arException;
use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEngine\Exception\InvalidContextException;

/**
 * Context
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
abstract class Context extends ActiveRecord {

	/**
	 * @var Specification[]|void
	 */
	protected $base_filter = null;


	/**
	 * @throws InvalidContextException
	 */
	static function returnDbTableName():string {
		throw new InvalidContextException(arException::UNKNONWN_EXCEPTION, 'Implement getConnectorContainerName in your child-class');
	}


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