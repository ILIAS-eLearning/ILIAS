<?php

namespace ILIAS\Context;

use ActiveRecord;
use arException;
use ILIAS\RuleEngine\Specification\Specification;
use ILIAS\RuleEngine\Exceptions\InvalidContextException;

/**
 * Context
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
abstract class Context extends ActiveRecord {

	/**
	 * @var string
	 */
	protected $table_name;
	/**
	 * @var Specification[]|void
	 */
	protected $base_filter = null;


	/**
	 * @throws InvalidContextException
	 */
	static function returnDbTableName() {
		throw new InvalidContextException(arException::UNKNONWN_EXCEPTION, 'Implement getConnectorContainerName in your child-class');
	}


	/**
	 * @param Specification[] $specification
	 *
	 * @return mixed
	 */
	public function findSatisfying(array $specifications) {

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