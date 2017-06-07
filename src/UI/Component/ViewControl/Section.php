<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;

use \ILIAS\UI\Component\Component;

/**
 * This describes a Section Control
 */
interface Section extends Component{

	/**
	 * Returns the action executed by clicking on previous.
	 *
	 * @return string action
	 */
	public function getPreviousActions();

	/**
	 * Returns the action executed by clicking on next.
	 *
	 * @return string action
	 */
	public function getNextActions();

	/**
	 * Returns the Default- or Split-Button placed in the middle of the control
	 *
	 * @return \ILIAS\UI\Component\Component the Default- or Split-Button placed in the middle of the control
	 */
	public function getSelectorButton();

}
