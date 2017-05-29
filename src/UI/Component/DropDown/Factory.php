<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\DropDown;

interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The standard dropdown is the default dropdown to be used in ILIAS. If
	 *       there is no good reason using another dropdown instance in ILIAS, this
	 *       is the one that should be used.
	 *   composition: >
	 *       The standard dropdown uses the primary color as background.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Standard dropdown MUST be used if there is no good reason using
	 *          another instance.
	 * ---
	 * @param \ILIAS\UI\Component\DropDown/DropDownItem[] array of action items
	 * @return \ILIAS\UI\Component\DropDown\Standard
	 */
	public function standard($items);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Drop down items represent single entries in a drop down.
	 *   composition: >
	 *       Drop down items cannot be rendered on their own, instead they are passed
	 *       to a \ILIAS\UI\Component\DropDown\DropDown instance.
	 *
	 * ---
	 * @param string $label
	 * @param string $action
	 * @return \ILIAS\UI\Component\DropDown\DropDownItem
	 */
	public function item($label, $action);
}
