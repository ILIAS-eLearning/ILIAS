<?php

/**
 * Class ilWACLogDummy
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWACLogDummy {

	/**
	 * @var array
	 */
	protected $storage = array();


	/**
	 * @param $dummy
	 */
	public function write($dummy) {
		$this->storage[] = $dummy;
	}
}
