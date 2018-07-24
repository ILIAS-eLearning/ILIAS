<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;
//use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\TriggeredSignal;


/**
 * This describes a toggle button.
 */
interface Toggle extends Button {

	/**
	 * Get to know if the Toggle Button is on or off.
	 *
	 * @return bool
	 */
	public function isOn();

	/**
	 * Get the action of the Toggle Button when it is off.
	 *
	 * @return	string|TriggeredSignal
	 */
	public function getActionOff();

	//public function withOnClickOff(Signal $signal);

}