<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button\Split;

/**
 * This describes commonalities between various split buttons.
 */
interface Split extends \ILIAS\UI\Component\Component {
	/**
	 * Get to know if the split button is activated.
	 *
	 * @return 	bool
	 */
	public function isActive();

	/**
	 * Get a split button like this, but action should be unavailable atm.
	 *
	 * The button will still have an action afterwards, this might be usefull
	 * at some point where we want to reactivate the button client side.
	 *
	 * @return Split
	 */
	public function withUnavailableActions();
}
