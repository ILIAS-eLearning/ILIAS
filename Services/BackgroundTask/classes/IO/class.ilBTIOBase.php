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
	 * @return string Gets a hash for this IO. If two objects are the same the hash must be the same! if two objects are different you need to have
	 *                as view collitions as possible.
	 **/
	public function getHash() {
		return md5($this->serialize());
	}


	/**
	 * @param \ilBackgroundTaskIO $other
	 * @return bool
	 */
	public function equals(ilBackgroundTaskIO $other) {
		return get_class($this) == get_class($other) && $this->serialize() == $other->serialize();
	}
}