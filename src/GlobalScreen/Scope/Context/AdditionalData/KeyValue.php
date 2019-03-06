<?php namespace ILIAS\GlobalScreen\Scope\Context\AdditionalData;

/**
 * Class KeyValue
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class KeyValue implements \ArrayAccess {

	/**
	 * @var array
	 */
	private $values = [];


	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset) {
		return isset($this->values[$offset]);
	}


	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset) {
		return $this->values[$offset];
	}


	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value) {
		$this->values[$offset] = $value;
	}


	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset) {
		unset($this->values[$offset]);
	}
}
