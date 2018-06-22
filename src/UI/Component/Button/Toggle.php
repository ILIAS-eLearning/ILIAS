<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * This describes a toggle button.
 */
interface Toggle extends Button {

	/**
	 * Get the activation action of the toggle button, i.e. a signal the toggle button triggers on click.
	 *
	 * @return	string|(Signal[])
	 */
	public function getActionActivated();

	/**
	 * Get the deactivation action of the toggle button, i.e. a signal the toggle button triggers on click.
	 *
	 * @return	string|(Signal[])
	 */
	public function getActionDeactivated();

}