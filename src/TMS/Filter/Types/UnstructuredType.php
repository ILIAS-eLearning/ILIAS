<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

/**
 */
abstract class UnstructuredType extends Type {
	/**
	 * @inheritdocs
	 */
	public function unflatten(array &$value) {
		$name = $this->repr();
		if (count($value) == 0) {
			throw new \InvalidArgumentException("Expected $name, found nothing.");
		}

		$val = array_shift($value);
		if (!$this->contains($val)) {
			throw new \InvalidArgumentException("Expected $name, found '$val'");
		}
		return $val;
	}

	/**
	 * @inheritdocs
	 */
	public function flatten($value) {
		return array($value);
	}
}
