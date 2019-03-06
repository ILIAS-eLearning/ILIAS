<?php namespace ILIAS\GlobalScreen\Scope\Context;

use ILIAS\GlobalScreen\Scope\Context\Stack\CalledContexts;

/**
 * Class ContextServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContextServices {

	/**
	 * @return CalledContexts
	 */
	public function stack(): CalledContexts {
		return new CalledContexts();
	}
}
