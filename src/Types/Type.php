<?php

namespace ILIAS\Types;

interface Type {

	/**
	 * @return string A string representation of the Type.
	 */
	function __toString();

	/**
	 * Is this type a subtype of $type. Not strict! x->isSubtype(x) == true.
	 *
	 * @param Type $type ValueType
	 * @return bool
	 */
	function isSubtypeOf(Type $type);

	/**
	 * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
	 *
	 * @return Type[]
	 */
	function getAncestors();

	/**
	 * returns true if the two types are equal.
	 *
	 * @param Type $otherType
	 * @return bool
	 */
	function equals(Type $otherType);
}