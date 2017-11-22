<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

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
	public function repr() {
		return "(".implode("|", array_map(function($t) {return $t->repr();}, $this->sub_types)).")";
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

	/**
	 * @inheritdocs
	 */
	public function unflatten(array &$value) {
		$choice = array_shift($value);
		$val = array_shift($value);
		$name = $this->repr();
		if (!$this->contains(array($choice,$val))) {
			throw new \InvalidArgumentException("Expected $name, found $choice:'$val'");
		}
		return array($choice, $val);
	}

	/**
	 * @inheritdocs
	 */
	public function flatten($value) {
		return $value;
	}
}
