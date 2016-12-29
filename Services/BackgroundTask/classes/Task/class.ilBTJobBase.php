<?php

/**
 * Class ilBTJob
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilBTJobBase extends ilBTTaskBase implements ilBTJob {

	/**
	 * @return bool
	 */
	public function isJob() {
		return true;
	}


	/**
	 * @return bool
	 */
	public function isUserInteraction() {
		return false;
	}
}
