<?php

/**
 * Class ilWACLogDummy
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWACLogDummy {

	/**
	 * @param $dummy
	 */
	public function write($dummy) {
		unset($dummy);
	}
}
