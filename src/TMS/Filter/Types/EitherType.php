<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

/**
 */
class EitherType extends Type {
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
		return "(".implode("|", array_map(function($t) {return $t->repr();}), $this->sub_types).")";
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		foreach ($this->sub_types as $sub_type) {
			if ($sub_type->contains($value)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritdocs
	 */
	public function unflatten(array &$value) {
		throw new \Exception("NYI!");
	}

	/**
	 * @inheritdocs
	 */
	public function flatten($value) {
		throw new \Exception("NYI!");
	}

}
