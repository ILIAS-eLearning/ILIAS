<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Types;

/**
 */
class ClassType extends UnstructuredType {
	/**
	 * @var	string
	 */
	private $cls_name;

	public function __construct($cls_name) {
		assert('is_string($cls_name)');
		$this->cls_name = $cls_name;
	}

	/**
	 * @inheritdocs
	 */
	public function repr() {
		return $this->cls_name;
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		return $value instanceof $this->cls_name;
	}
}