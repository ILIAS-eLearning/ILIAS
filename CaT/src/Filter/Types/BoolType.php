<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Types;

/**
 */
class BoolType extends Type {
	/**
	 * @inheritdocs
	 */
	public function contains($value) {
		return is_bool($value);
	}
}