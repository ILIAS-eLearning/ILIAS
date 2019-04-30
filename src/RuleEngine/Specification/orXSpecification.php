<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Class orX
 *
 * @package ILIAS\RuleEngine\Specification
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
class orXSpecification extends Composite {

	public function __construct(array $specifications = []) {
		parent::__construct('OR', $specifications);
	}
}