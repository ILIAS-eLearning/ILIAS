<?php

/**
 * Class ilBackgroundTaskStorage
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilBackgroundTaskStorage {

	/**
	 * @param \ilBTBucket $bucket
	 * @throws \Exception
	 */
	public function addBucket(ilBTBucket $bucket) {
		$chain_errors = $bucket->checkChain();
		if (count($chain_errors) > 0) {
			throw new Exception(implode(", ", $chain_errors));
		}//TODO other exception

	}
}