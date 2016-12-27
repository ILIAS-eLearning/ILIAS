<?php

/**
 * Class ilBackgroundTasksIO
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 *
 */
abstract class ilBackgroundTaskIO implements ilBTIO {

	/**
	 * @return string JSON-Representation of the object-members
	 */
	public function serialize() {
		$values = array();
		foreach ($this as $k => $v) {
			$values[$k] = $v;
		}

		return json_encode($values);
	}


	/**
	 * @param string $serialized JSON-Representation of the object-members
	 * @return void
	 */
	public function unserialize($serialized) {
		$obj = json_decode($serialized);
		foreach ($obj as $k => $v) {
			$this->{$k} = $v;
		}
	}

  /**
   * @return string By default we just use the md5 value of the serialization.
   **/
	public function getHash() {
	  return md5($this->serialize);
	}

	/**
	 * @param ilBackgroundTaskIO $other
	 * @return bool
	 */
	public function equals(ilBackgroundTaskIO $other) {
	  return get_class($this) == get_class($other) && $this->serialize() == $other->serialize();
	}

	/**
	 * @return string The default is to just take the class name.
	 */
	public function getType() {
		return get_class($this);
	}
}