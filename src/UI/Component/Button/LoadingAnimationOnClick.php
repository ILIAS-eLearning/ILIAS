<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * Interface for buttons with loading animation on click
 *
 * @author	killing@leifos.de
 */
interface LoadingAnimationOnClick {
	/**
	 * If clicked the button will display a spinner
	 * wheel to show that a request is being processed
	 * in the background.
	 *
	 * @param 	bool 	$anim
	 * @return 	self
	 */
	public function withLoadingAnimationOnClick($anim);

	/**
	 * Return whether loading animation has been activated
	 *
	 * @return 	bool
	 */
	public function hasLoadingAnimationOnClick();
}
