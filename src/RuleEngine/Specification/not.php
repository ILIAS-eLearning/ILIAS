<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Class Not
 *
 * @package ILIAS\RuleEngine\Specification
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
class Not extends Composite {

	public function __construct(Specification $specification) {
		parent::__construct('NOT', $specification);
	}
}