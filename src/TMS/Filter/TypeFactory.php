<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter;

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

	public function either(/* ... $sub_types */) {
		$sub_types = func_get_args();
		return new Types\EitherType($sub_types);
	}

	public function lst(Types\Type $of_type) {
		return new Types\ListType($of_type);
	}

	public function option(/* ... $sub_types */) {
		$sub_types = func_get_args();
		return new Types\OptionType($sub_types);
	}

	public function dict(array $types ) {
		return new Types\DictionaryType($types);
	}
}
