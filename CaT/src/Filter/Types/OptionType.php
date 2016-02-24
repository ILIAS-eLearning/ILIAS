<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Types;

/**
 */
class OptionType extends Type {
	/**
	 * @var	Type[]
	 */
	private $sub_types;

	public function __construct(array $sub_types) {
		$this->sub_types = array_map(function(Type $t) { return $t; }, $sub_types);
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		if (!is_array($value)
		or count($value) != 2
		or !is_int($value[0])
		or $value[0] >= count($this->sub_types)
		or $value[0] < 0) {
			
			return false;
		}

		return $this->sub_types[$value[0]]->contains($value[1]);
	}
}