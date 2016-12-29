<?php

/**
 * Class ilBTIO
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
interface ilBTIO extends Serializable {

	/**
	 * @return string Gets a hash for this IO. If two objects are the same the hash must be the same! if two objects are different you need to have
	 *                as view collitions as possible.
	 */
	public function getHash();


	/**
	 * @param ilBackgroundTaskIO $other
	 * @return bool
	 */
	public function equals(ilBackgroundTaskIO $other);


	/**
	 * @var string get the Type of the
	 */
	public function getType();
}
