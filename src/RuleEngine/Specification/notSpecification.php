<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Class NotSpecification
 *
 * @package ILIAS\RuleEngine\Specification
 */
class NotSpecification extends Composite {

	public function __construct(Specification $specification) {
		parent::__construct('NOT', [$specification]);
	}
}