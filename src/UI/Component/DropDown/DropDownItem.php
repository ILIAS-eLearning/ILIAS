<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\DropDown;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Drop down items
 */
interface DropDownItem extends Component, JavaScriptBindable, Clickable, Hoverable {
	/**
	 * Get the label of the item.
	 *
	 * @return	string
	 */
	public function getLabel();

	/**
	 * Get the action of the item
	 *
	 * @return	string
	 */
	public function getAction();
}
