<?php

namespace ILIAS\BackgroundTasks\Implementation\Values;

use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\ValueType;

/**
 * Class ThunkValue
 * @package ILIAS\BackgroundTasks\Implementation\Values
 *
 * Represents a value that has not yet been calculated.
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ThunkValue extends AbstractValue {

	/**
	 * @var ValueType
	 */
	protected $type;

	/**
	 * ThunkValue constructor.
	 * @param $type ValueType What type of value will be stored here later?
	 */
	public function __construct($type) {
		$this->type = $type;
	}

	/**
	 * @return \ILIAS\BackgroundTasks\ValueType
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * String representation of object
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize() {
		return null;
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
		// Nothing to do.
	}

	/**
	 * @return string Gets a hash for this IO. If two objects are the same the hash must be the same! if two objects are different you need to have
	 *                as view collitions as possible.
	 */
	public function getHash() {
		return null;
	}

	/**
	 * @param \ILIAS\BackgroundTasks\Value $other
	 * @return mixed
	 */
	public function equals(Value $other) {
		return false;
	}
}