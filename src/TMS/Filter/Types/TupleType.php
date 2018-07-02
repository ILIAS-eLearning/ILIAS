<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

/**
 */
class TupleType extends Type {
	/**
	 * @var	Type[]
	 */
	private $sub_types;

	public function __construct(array $sub_types) {
		$this->sub_types = array_map(function(Type $t) { return $t; }, $sub_types);
	}

	public function item_types() {
		return $this->sub_types;
	}

	/**
	 * @inheritdocs
	 */
	public function repr() {
		return "(".implode(",", array_map(function($t) {return $t->repr();}, $this->sub_types)).")";
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		if (!is_array($value) or count($value) != count($this->sub_types)) {
			return false;
		}

		for ($i = 0; $i < count($value); $i++) {
			if (!$this->sub_types[$i]->contains($value[$i])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @inheritdocs
	 */
	public function unflatten(array &$value) {
		$vals = array();
		foreach ($this->sub_types as $sub_type) {
			$vals[] = $sub_type->unflatten($value);
		}
		return $vals;
	}

	/**
	 * @inheritdocs
	 */
	public function flatten($value) {
		$res = array();
		$len = count($this->sub_types);
		for ($i = 0; $i < $len; $i++) {
			$res = array_merge($res, $this->sub_types[$i]->flatten($value[$i]));
		}
		return $res;
	}
}
