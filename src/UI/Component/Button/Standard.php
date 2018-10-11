<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use \ILIAS\UI\Component\Counter\Counter;

/**
 * This describes a standard button. 
 */
interface Standard extends Button {
	/**
	 * If clicked the button will display a spinner
	 * wheel to show that a request is being processed
	 * in the background.
	 *
	 * @param 	bool 	$anim
	 * @return 	self
	 */
	public function withLoadingAnimation($anim);

	/**
	 * Return whether loading animation has been activated
	 *
	 * @return 	bool
	 */
	public function hasLoadingAnimation();

}
