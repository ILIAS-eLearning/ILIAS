<?php

namespace ILIAS\BackgroundTasks;

interface ValueType {

	/**
	 * @return string A string representation of the Type.
	 */
	function __toString();

	/**
	 * Is this type a subtype of $type. Not strict! x->isSubtype(x) == true.
	 *
	 * @param $type ValueType
	 * @return bool
	 */
	function isSubtypeOf(ValueType $type);

	/**
	 * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
	 *
	 * @return ValueType[]
	 */
	function getAncestors();

	/**
	 * returns true if the two types are equal.
	 *
	 * @param ValueType $otherType
	 * @return bool
	 */
	function equals(ValueType $otherType);
}