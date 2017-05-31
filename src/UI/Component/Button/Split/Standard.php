<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button\Split;

/**
 * This describes a standard split button.
 */
interface Standard extends Split {
	/**
	 * Get the array containing the actions and labels of the split button
	 *
	 * @return	string
	 */
	public function getActionsAndLabels();
}
