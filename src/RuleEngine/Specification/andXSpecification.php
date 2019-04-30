<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Class AndX
 *
 * @package ILIAS\RuleEngine\Specification
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
class AndXSpecification extends Composite {

	public function __construct(array $specifications = []) {
		parent::__construct('AND', $specifications);
	}
}