<?php

namespace ILIAS\BackgroundTasks\Implementation\ValueTypes;

use ILIAS\BackgroundTasks\ValueType;

class SingleType implements ValueType {

	/** @var \ReflectionClass */
	protected $type;

	/**
	 * SingleType constructor.
	 * @param $fullyQualifiedClassName
	 */
	public function __construct(string $fullyQualifiedClassName) {
		$this->type = new \ReflectionClass($fullyQualifiedClassName);
	}

	/**
	 * @return string A string representation of the Type.
	 */
	function __toString() {
		return $this->type->getName();
	}

	/**
	 * @param ValueType $type
	 * @return bool
	 */
	function isSubtypeOf(ValueType $type) {
		if(!$type instanceof SingleType)
			return false;

		return $this->type->isSubclassOf($type->__toString()) || $this->__toString() == $type->__toString();
	}

	/**
	 * returns the hierarchy of this type. E.g. ["AbstractValue", "ScalarValue", "IntegerValue", "UserIdValue"]
	 *
	 * @return ValueType[]
	 */
	function getAncestors() {
		$class = $this->type;
		$ancestors = [new SingleType($class->getName())];

		while($class = $class->getParentClass())
			$ancestors[] = new SingleType($class->getName());

		return array_reverse($ancestors);
	}

	/**
	 * returns true if the two types are equal.
	 *
	 * @param $otherType
	 * @return bool
	 */
	function equals(ValueType $otherType) {
		if(!$otherType instanceof SingleType)
			return false;

		return $this->__toString() == $otherType->__toString();
	}
}
