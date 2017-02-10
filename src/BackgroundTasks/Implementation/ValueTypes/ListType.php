<?php

namespace ILIAS\BackgroundTasks\Implementation\ValueTypes;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\ValueType;

class ListType implements ValueType {

	/** @var ValueType */
	protected $type;

	/**
	 * SingleType constructor.
	 * @param $valueType Give a Value Type or a Type that will be wrapped in a single type.
	 */
	public function __construct($valueType) {
		if(!is_a($valueType, ValueType::class))
			$valueType = new SingleType($valueType);
		$this->type = $valueType;
	}

	/**
	 * @return string A string representation of the Type.
	 */
	function __toString() {
		return $this->type . "[]";
	}

	/**
	 * Is this type a subtype of $type. Not strict! x->isSubtype(x) == true.
	 *
	 * @param $type ValueType
	 * @return bool
	 */
	function isSubtypeOf(ValueType $type) {
		if(!$type instanceof ListType)
			return false;

		return $this->type->isSubtypeOf($type->getType());
	}

	/**
	 * @return ValueType
	 */
	function getType() {
		return $this->type;
	}

	/**
	 * @param $list ValueType[]
	 * @return ValueType
	 * @throws InvalidArgumentException
	 */
	public static function calculateLowestCommonType($list) {
		// If the list is empty the type should be [] (empty list).
		if(!count($list))
			return null;

		if(count($list) == 1)
			return $list[0];

		$ancestorsList = [];
		foreach ($list as $object) {
			if(!$object instanceof ValueType)
				throw new InvalidArgumentException("List Type must be constructed with instances of ValueType.");
			$ancestorsList[] = $object->getAncestors();
		}

		$lct = $ancestorsList[0][0];
		foreach ($ancestorsList[0] as $i => $ancestors) {
			if(static::sameClassOnLevel($ancestorsList, $i)) {
				$lct = $ancestors;
			} else {
				return $lct;
			}
		}
		return $lct;
	}

	/**
	 * @param $ancestorsList ValueType[][]
	 * @param $i
	 * @return bool
	 */
	protected static function sameClassOnLevel($ancestorsList, $i) {
		$class = $ancestorsList[0][$i];
		foreach($ancestorsList as $class_hierarchy) {
			if(count($class_hierarchy) <= $i)
				return false;
			if(!$class_hierarchy[$i]->equals($class))
				return false;
		}
		return true;
	}

	/**
	 * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
	 *
	 * @return ValueType[]
	 */
	public function getAncestors() {
		$ancestors = [];

		foreach ($this->type->getAncestors() as $type) {
			$ancestors[] = new ListType($type);
		}

		return $ancestors;
	}

	/**
	 * returns true if the two types are equal.
	 *
	 * @param ValueType $otherType
	 * @return bool
	 */
	function equals(ValueType $otherType) {
		if(!$otherType instanceof ListType)
			return false;

		return $this->type->equals($otherType->getType());
	}
}