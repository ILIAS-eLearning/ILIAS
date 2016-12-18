<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;

/**
 * This describes a Mode Control
 */
interface Mode extends \ILIAS\UI\Component\Component {
	/**
	 * set the currently active Button by label.
	 *
	 * @param string $label. The label of the button to activate
	 */
	public function withActive($label);

	/**
	 * get the label of the currently active button of the mode control
	 *
	 * @return string the label of the currently active button of the mode control
	 */
	public function getActive();

	/**
	 * Get the array containing the actions and labels of the mode control
	 *
	 *@return array (string|string)[]. Array containing keys as label and values as actions.
	 */
	public function getLabelledActions();
}
