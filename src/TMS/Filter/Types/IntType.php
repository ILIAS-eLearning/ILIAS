<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

/**
 */
class IntType extends UnstructuredType {
	/**
	 * @inheritdocs
	 */
	public function repr() {
		return "int";
	}

	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		return is_int($value);
	}
}
