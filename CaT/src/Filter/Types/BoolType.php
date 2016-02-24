<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Types;

/**
 */
class BoolType extends UnstructuredType {
	/**
	 * @inheritdocs
	 */
	public function repr() {
		return "bool";
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		return is_bool($value);
	}
}