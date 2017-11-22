<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

/**
 */
class ListType extends Type {
	/**
	 * @var	Type
	 */
	private $item_type;

	public function __construct(Type $item_type) {
		$this->item_type = $item_type;
	}

	/**
	 * @inheritdocs
	 */
	public function repr() {
		return "[".$this->item_type->repr()."]";
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		if (!is_array($value)) {
			return false;
		}
		foreach($value as $val) {
			if (!$this->item_type->contains($val)) {
				return false;
			}
		}

		return true;
	}

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
