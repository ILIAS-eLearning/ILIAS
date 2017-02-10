<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\AggregationValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Implementation\Values\PrimitiveValueWrapperFactory;
use ILIAS\BackgroundTasks\Implementation\ValueTypes\ListType;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\ValueType;

/**
 * Class ListValue
 * @package ILIAS\BackgroundTasks\Implementation\Values
 *
 * The type of the class will be the lowest common type in the list e.g. IntegerValue[].
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ListValue extends AbstractValue {

	/**
	 * @var array The values of the list are saved in an array.
	 */
	protected $list = array();

	/**
	 * @var ValueType
	 */
	protected $type;

	/**
	 * ListValue constructor.
	 * @param $list array
	 */
	public function __construct($list) {
		$wrapperFactory = PrimitiveValueWrapperFactory::getInstance();
		$types = [];
		foreach ($list as $value) {
			$valueWrapped = $wrapperFactory->wrapValue($value);
			$this->list[] = $valueWrapped;
			$types[] =  $valueWrapped->getType();
		}

		$this->type = ListType::calculateLowestCommonType($types);
	}

	/**
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() {
		return serialize($this->list);
	}

	/**
	 * Constructs the object
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized) {
		$this->list = $this->unserialize($serialized);
		$this->type = $this->calculateLowestCommonType($this->list);
	}

	/**
	 * @return string Gets a hash for this IO. If two objects are the same the hash must be the same! if two objects are different you need to have
	 *                as few collisions as possible.
	 */
	public function getHash() {
		$hashes = '';
		foreach ($this->getList() as $value)
			$hashes .= $value->getHash();

		return md5($hashes);
	}

	/**
	 * @param \ILIAS\BackgroundTasks\Value $other
	 * @return mixed
	 */
	public function equals(Value $other) {
		if(!$other instanceof ListValue)
			return false;

		if($this->getType() != $other->getType())
			return false;

		$values = $this->getList();
		$otherValues = $other->getList();

		if(count($values) != count($otherValues))
			return false;

		for($i = 0; $i < count($values); $i++) {
			if(!$values[$i]->equals($otherValues[$i]));
		}

		return true;
	}

	/**
	 * @return Value[]
	 */
	public function getList() {
		return $this->list;
	}

	/**
	 * @param $list Value
	 * @return string
	 */
	protected function calculateLowestCommonType($list) {
		// If the list is empty the type should be [] (empty list).
		if(!count($list))
			return "";

		$class_hierarchies = [];
		foreach ($list as $object) {
			$class_hierarchies[] = array_reverse($this->getClassHierarchy($object));
		}

		$lct = $class_hierarchies[0][0];
		foreach ($class_hierarchies[0] as $i => $class) {
			if($this->sameClassOnLevel($class_hierarchies, $i)) {
				$lct = $class;
			} else {
				return $lct;
			}
		}
	}

	/**
	 * @param $class_hierarchies
	 * @param $i
	 * @return bool
	 */
	protected function sameClassOnLevel($class_hierarchies, $i) {
		$class = $class_hierarchies[0][$i];
		foreach($class_hierarchies as $class_hierarchy) {
			if(count($class_hierarchy) <= $i)
				return false;
			if($class_hierarchy[$i] !== $class)
				return false;
		}
		return true;
	}

	/**
	 * @param $object
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function getClassHierarchy($object) {
		if (!is_object($object))
			throw new InvalidArgumentException("Given Value $object must be an object.");

		$hierarchy = [];
		$class = get_class($object);

		do {
			$hierarchy[] = $class;
		} while (($class = get_parent_class($class)) !== false);
		return $hierarchy;
	}

	/**
	 * @return ValueType
	 */
	public function getType() {
		return new ListType($this->type);
	}
}