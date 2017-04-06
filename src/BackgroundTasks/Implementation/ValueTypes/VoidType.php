<?php

use ILIAS\BackgroundTasks\ValueType;

class VoidValue implements ValueType {

	protected static $instance = null;

	/**
	 * Just to make it protected.
	 * VoidValue constructor.
	 */
	protected function __construct() {
	}

	/**
	 * @return VoidValue
	 */
	public static function instance(){
		if(!self::instance())
			self::$instance = new VoidValue();
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
	 * @param $type ValueType
	 * @return bool
	 */
	function isSubtypeOf(ValueType $type) {
		return $type instanceof VoidValue;
	}

	/**
	 * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
	 *
	 * @return ValueType[]
	 */
	function getAncestors() {
		return [self::class];
	}

	/**
	 * returns true if the two types are equal.
	 *
	 * @param ValueType $otherType
	 * @return bool
	 */
	function equals(ValueType $otherType) {
		return $otherType instanceof VoidValue;
	}
}