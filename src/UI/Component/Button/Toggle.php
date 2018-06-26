<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * This describes a toggle button.
 */
interface Toggle extends Button {

	/**
	 * Get the action of the Toggle Button when it is deactivated.
	 *
	 * @return	string|(Signal[])
	 */
	public function getActionDeactivated();

}