<?php

/**
 * Class ilBTJob
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilBTJobBase extends ilBTTaskBase implements ilBTJob {

	public function isJob() {
		return true;
	}

	public function isUserInteraction() {
		return false;
	}
}
