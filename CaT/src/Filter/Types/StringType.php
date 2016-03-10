<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Types;

/**
 */
class StringType extends UnstructuredType {
	/**
	 * @inheritdocs
	 */
	public function repr() {
		return "string";
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		return is_string($value);
	}
}