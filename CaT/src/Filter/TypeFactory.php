<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
 * Factory to build filters.
 *
 * A filter is a way to build a predicate from some inputs.
 */
class TypeFactory {
	public function int() {
		return new Types\IntType();
	}

	public function string() {
		return new Types\StringType();
	}

	public function bool() {
		return new Types\BoolType();
	}

	public function tuple(/* ... $sub_types */) {
		$sub_types = func_get_args();
		return new Types\TupleType($sub_types);
	}

	public function cls($cls_name) {
		return new Types\ClassType($cls_name);
	}
}