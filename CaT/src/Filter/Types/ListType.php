<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Types;

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
}