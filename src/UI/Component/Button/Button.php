<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes commonalities between standard and primary buttons. 
 */
interface Button extends \ILIAS\UI\Component\Component, JavaScriptBindable {
	/**
	 * Get the label on the button.
	 *
	 * @return	string
	 */
	public function getLabel();

	/**
	 * Get a button like this, but with an additional/replaced label.
	 *
	 * @param	string	$label
	 * @return	Button
	 */
	public function withLabel($label);

	/**
	 * Get the action of the button
	 *
	 * @return	string
	 */
	public function getAction();

	/**
	 * Get to know if the button is activated.
	 *
	 * @return 	bool
	 */
	public function isActive();

	/**
	 * Get a button like this, but action should be unavailable atm.
	 *
	 * The button will still have an action afterwards, this might be usefull
	 * at some point where we want to reactivate the button client side.
	 *
	 * @return Button
	 */
	public function withUnavailableAction();
}
