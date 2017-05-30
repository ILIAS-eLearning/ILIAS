<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\DropDown;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes commonalities between all types of drop downs
 */
interface DropDown extends Component, JavaScriptBindable, Clickable, Hoverable {

	/**
	 * Get the items of the drop down.
	 *
	 * @return	DropDownItem[]
	 */
	public function getItems();

	/**
	 * Get the label on the drop down.
	 *
	 * @return	string
	 */
	public function getLabel();

	/**
	 * Get a drop down like this, but with an additional/replaced label.
	 *
	 * @param	string	$label
	 * @return	DropDown
	 */
	public function withLabel($label);

}
