<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Class AndXSpecification
 *
 * @package ILIAS\RuleEngine\Specification
 */
class AndXSpecification extends Composite {

	public function __construct(array $specifications = []) {
		parent::__construct('AND', $specifications);
	}
}