<?php

namespace ILIAS\Types;

class TupleType implements Type {

	/**
	 * @var Type[]
	 */
	protected $types = [];

	/**
	 * SingleType constructor.
	 * @param $fullyQualifiedClassNames (string|Type)[] Give a Value Type or a Type that will be wrapped in a single type.
	 */
	public function __construct($fullyQualifiedClassNames) {
		foreach ($fullyQualifiedClassNames as $fullyQualifiedClassName) {
			if(!is_a($fullyQualifiedClassName, Type::class))
				$fullyQualifiedClassName = new SingleType($fullyQualifiedClassName);
			$this->types[] = $fullyQualifiedClassName;
		}
	}

	/**
	 * @return string A string representation of the Type.
	 */
	function __toString() {
		return "(" . implode(", ", $this->types) . ")";
	}

	/**
	 * Is this type a subtype of $type. Not strict! x->isSubtype(x) == true.
	 *
	 * @param Type $type ValueType
	 * @return bool
	 */
	function isSubtypeOf(Type $type) {
		if (! $type instanceof TupleType)
			return false;

		$others = $type->getTypes();
		for ($i = 0; $i < count($this->types); $i++) {
			if (!$this->types[$i]->isSubtypeOf($others[$i]))
				return false;
		}

		return true;
	}

	public function getTypes() {
		return $this->types;
	}

	/**
	 * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
	 *
	 * @return Type[]
	 */
	function getAncestors() {
		// TODO: Implement getAncestors() method.
	}

	/**
	 * returns true if the two types are equal.
	 *
	 * @param Type $otherType
	 * @return bool
	 */
	function equals(Type $otherType) {
		// TODO: Implement equals() method.
	}
}