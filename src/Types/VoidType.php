<?php

namespace ILIAS\Types;


class VoidType implements Type {

	protected static $instance = null;

	/**
	 * Just to make it protected.
	 * VoidValue constructor.
	 */
	protected function __construct() {
	}

	/**
	 * @return VoidType
	 */
	public static function instance(){
		if(!self::instance())
			self::$instance = new VoidType();
		return self::$instance;
	}

	/**
	 * @return string A string representation of the Type.
	 */
	function __toString() {
		return "Void";
	}

	/**
	 * Is this type a subtype of $type. Not strict! x->isSubtype(x) == true.
	 *
	 * @param $type Type
	 * @return bool
	 */
	function isSubtypeOf(Type $type) {
		return $type instanceof VoidType;
	}

	/**
	 * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
	 *
	 * @return Type[]
	 */
	function getAncestors() {
		return [self::class];
	}

	/**
	 * returns true if the two types are equal.
	 *
	 * @param Type $otherType
	 * @return bool
	 */
	function equals(Type $otherType) {
		return $otherType instanceof VoidType;
	}
}
