<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Represents a specification as in the Specification pattern.
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
interface Specification {


	public function getKey();

	public function getValue();

	public function getOperator();
}