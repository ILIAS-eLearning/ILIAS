<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Floatable;

use ILIAS\UI\Component\Component;

/**
 * Floatable base interface.
 */
interface Floatable extends Component {
	/**
	 * Get a floatable like this with a set of actions displayed as buttons.
	 *
	 * @param array<\ILIAS\UI\Component\Button\Button> $actions
	 * @return	\ILIAS\UI\Component\Floatable\Floatable
	 */
	public function withActions($actions);

	/**
	 * Get the items of the Dropdown.
	 *
	 * @return	array<\ILIAS\UI\Component\Button\Button>
	 */
	public function getActions();

	/**
	 * Get the title of the Popover.
	 *
	 * @return	string
	 */
	public function getTitle();

	/**
	 * Get the component representing the content of the floatable.
	 *
	 * @return Component
	 */
	public function getContent();
}
