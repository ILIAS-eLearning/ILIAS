<?php

/**
 * Class ilBTIO
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
interface ilBTIO extends Serializable {

	/**
	 * @return string
	 */
	public function getHash();


	/**
	 * @param \ilBackgroundTaskIO $other
	 * @return bool
	 */
	public function equals(ilBackgroundTaskIO $other);
}
