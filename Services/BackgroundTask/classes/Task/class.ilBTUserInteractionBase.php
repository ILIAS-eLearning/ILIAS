<?php

/**
 * Class ilBTUserInteractionBase
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilBTUserInteractionBase extends ilBTTaskBase implements ilBTUserInteraction {

	/**
	 * @return bool
	 */
	public function isUserInteraction() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function isJob() {
		return false;
	}
}