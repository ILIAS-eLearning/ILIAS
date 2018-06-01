<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Alert;

/**
 * Common interface to all alerts.
 */
interface Alert extends \ILIAS\UI\Component\Component {
	/**
	 * Get an Alert like this, but with a button.
	 *
	 * @param	Button $button
	 * @return	Alert
	 */
	public function withButton(Button $button);

}