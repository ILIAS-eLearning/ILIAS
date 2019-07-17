<?php
/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container;

/**
 * This is what a factory for input containers looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Forms are used to let the user enter or modify data, check her inputs
	 *      and submit them to the system.
	 *      Forms arrange their contents (i.e. fields) in an explanatory rather
	 *      than space-saving way.
	 *   composition: >
	 *      Forms are composed of input fields, displaying their labels and bylines.
	 *   rivals:
	 *      filter: >
	 *          Filters are used to limit search results; they never modify data in
	 *          the system.
	 *
	 *
	 * ---
	 * @return  \ILIAS\UI\Component\Input\Container\Form\Factory
	 */
	public function form();

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Filters are used to let the user limit content within a table, list or any other collection of items
	 *      presented on the screen.
	 *   composition: >
	 *      Filters are composed of two visually separated areas:
	 *      First, there is the Filter Bar at the top. It contains an Expand/Collapse Glyph on the left side.
	 *      On the right, an "Apply" Bulky Button, a "Reset" Bulky Button and a Toggle Button for activating/deactivating
	 *      the Filter are placed. An additional row with set filter settings will be shown at the bottom of the Filter Bar,
	 *      when the Filter Bar is collapsed and the Filter is activated at the same time.
	 *      Second, there is an area where the Input Fields are displayed. Every Input Field is rendered with a Label
	 *      on the left and a "Remove" Glyph on the right. The values, that are currently entered in the Input Fields,
	 *      are displayed as non-editable text and will get editable when the user focuses an Input Field (see effect).
	 *      After the last Input Field, an "Add" Bulky Button is shown if additional Input Fields can be added.
	 *   effect: >
	 *      In the Filter Bar:
	 *      Clicking on the Expand/Collapse Glyph expands/collapses the second area of the Filter,
	 *      where the Input Fields are placed. When the Filter Bar is expanded, the row with set filter settings
	 *      at the bottom of the Filter Bar will be hidden.
	 *      Clicking on the "Apply" Bulky Button applies the settings which the user has made for the Filter and
	 *      reloads the content of the item collection (e.g. Table) immediately.
	 *      Clicking on the "Reset" Bulky Button resets the Filter to the initial state and reloads the content
	 *      of the item collection immediately.
	 *      Clicking on the "Toggle" Button imitates a click on the "Apply" Bulky Button, activates/deactivates the Filter
	 *      and reloads the content of the item collection immediately.
	 *      In the Input Fields Area:
	 *      Clicking on an Input Field between its Label and its "Remove" Glyph shows up a Popover where the Input Field
	 *      is presented. Writing down a value into the Input Field in the Popover synchronizes the values
	 *      in both Input Fields.
	 *      Clicking on the "Remove" Glyph next to an Input Field makes this Input Field disappear from the Filter.
	 *      Clicking on the "Add" Bulky Button shows up a list with Labels of all possible Input Fields, which are
	 *      not part of the Filter yet, in a Popover. Clicking on one specific Input Field Label in this list adds
	 *      the selected Input Field to the Filter and imitates a click on it.
	 *   rivals:
	 *      forms: >
	 *          Unlike Filters, Forms are used to enter or modify data in the system.
	 *
	 * rules:
	 *   usage:
	 *     1: Filters MUST be used on the same page as tables or other collections of items.
	 *   interaction:
	 *     1: Input Fields outside of Popovers MUST NOT be editable, just clickable.
	 *   wording:
	 *     1: Labels of Input Fields MUST be shown shortened (with three dots at the end) when space is scarce.
	 *     2: The set filter settings in the bottom row of the Filter Bar MUST be shown shortened when space is scarce.
	 *   style:
	 *     1: The Filter Bar and the Input Fields Area SHOULD be separated visually, e.g. with a border-line.
	 *     2: The Toggle Button MUST NOT contain a Label.
	 *     3: The Popovers SHOULD be shown below the Input Field or the "Add" Bulky Button.
	 *   ordering:
	 *     1: A Filter MUST be placed above the item collection it acts upon.
	 *   responsiveness:
	 *     1: >
	 *        On screens larger than medium size, there MUST be three Input Fields per row. On medium-sized screens
	 *        or below, only one Input Field MUST be shown per row.
	 *   accessibility:
	 *     1: Input Fields MUST be accessible by keyboard by using the "Tab"-Key and clickable by using the "Return"-Key.
	 *
	 *
	 * ---
	 * @return  \ILIAS\UI\Component\Input\Container\Filter\Factory
	 */
	public function filter();
}
