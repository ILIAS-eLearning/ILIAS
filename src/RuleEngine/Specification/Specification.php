<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Represents a specification as in the Specification pattern.
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
interface Specification {

	/**
	 * @return int|string
	 */
	public function getRule();


	/**
	 * @return array
	 */
	public function getParameters(): array;
}