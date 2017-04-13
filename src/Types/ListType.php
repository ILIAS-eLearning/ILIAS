<?php

namespace ILIAS\Types;

use ILIAS\Types\Exceptions\InvalidArgumentException;

class ListType implements Type {

	/** @var Type */
	protected $type;

	/**
	 * SingleType constructor.
	 * @param $fullyQualifiedClassName string|Type Give a Value Type or a Type that will be wrapped in a single type.
	 */
	public function __construct($fullyQualifiedClassName) {
		if(!is_a($fullyQualifiedClassName, Type::class))
			$fullyQualifiedClassName = new SingleType($fullyQualifiedClassName);
		$this->type = $fullyQualifiedClassName;
	}

	/**
	 * @return string A string representation of the Type.
	 */
	function __toString() {
		return "[" . $this->type . "]";
	}

	/**
	 * Is this type a subtype of $type. Not strict! x->isSubtype(x) == true.
	 *
	 * @param $type Type
	 * @return bool
	 */
	function isSubtypeOf(Type $type) {
		if(!$type instanceof ListType)
			return false;

		return $this->type->isSubtypeOf($type->getContainedType());
	}

	/**
	 * @return Type
	 */
	function getContainedType() {
		return $this->type;
	}

	/**
	 * Todo: This implementation is not performing well (needs the most iterations) on lists with all the same type,
	 * this might be suboptimal.
	 *
	 * @param $list Type[]
	 * @return Type
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
			if(!$object instanceof Type)
				throw new InvalidArgumentException("List Type must be constructed with instances of Type.");
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

		// We reach this point if the types are equal.
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
	 * @param Type $otherType
	 * @return bool
	 */
	function equals(Type $otherType) {
		if(!$otherType instanceof ListType)
			return false;

		return $this->type->equals($otherType->getContainedType());
	}
}