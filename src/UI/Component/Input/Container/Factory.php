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
	 *      Filters are used to let the user limit content within a table.
	 *   composition: >
	 *      Filters are composed of two visually separated entities:
	 *      First, there is the Filter Bar at the top. It contains an Expand/Collapse Glyph on the left side
	 *      On the right, an "Apply" Bulky Button, a "Reset" Bulky Button and a Toggle Button are placed. An additional
	 *      row with set filter settings will be shown at the bottom of the Filter Bar, when the Filter Bar is collapsed
	 *      and the Filter is activated at the same time.
	 *      Second, there is an area where Filter Fields are displayed. Every Filter Item holds a Label on the left
	 *      and a "Remove" Glyph on the right. Every Filter Field stores an Input Field. After the last Filter Field,
	 *      an "Add" Bulky Button is always shown.
	 *   effect: >
	 *      In the Filter Bar:
	 *      Clicking on the Expand/Collapse Glyph expands/collapses the second entity of the Filter,
	 *      where the Filter Fields are placed. When the Filter Bar is expanded, the row with set filter settings
	 *      at the bottom will be hidden.
	 *      Clicking on the "Apply" Bulky Button applies the settings which the user has made for the Filter and
	 *      reloads the page immediately.
	 *      Clicking on the "Reset" Bulky Button resets the Filter to the initial state and reloads the page immediately.
	 *      Clicking on the Toggle Button imitates a click on the "Apply" Bulky Button, activates/deactivates the Filter
	 *      and reloads the page immediately.
	 *      In the Filter Fields Area:
	 *      Clicking on a Filter Field between its Label and its "Remove" Glyph shows up a Popover where an Input Field
	 *      is stored. Writing down a value into the Input Field synchronizes the value in the Input Field
	 *      with the value in the Filter Item.
	 *      Clicking on the "Remove" Glyph in a Filter Item makes a Filter Field being no part of the Filter anymore.
	 *      Clicking on the "Add" Bulky Button shows up a Popover where a list of Filter Items are stored which are not
	 *      part of the Filter yet. Clicking on one specific Filter Item in this list adds the selected Filter Item
	 *      to the Filter and imitates a click on it.
	 *   rivals:
	 *      forms: >
	 *          Unlike Filters, Forms are used to enter or modify data in the system.
	 *
	 * rules:
	 *   usage:
	 *     1: Filters MUST be used on the same page as tables.
	 *   interaction:
	 *     1: Filter Items MUST NOT be editable, just clickable.
	 *   wording:
	 *     1: Labels of Filter Items MUST be shown shortened (with three dots at the end) when space is scarce.
	 *     2: The set filter settings in the bottom row of the Filter Bar MUST be shown shortened when space is scarce.
	 *   style:
	 *     1: The Filter Bar and the Filter Fields Area SHOULD be separated visually, e.g. with a border-line.
	 *     2: The Toggle Button MUST NOT contain a Label.
	 *     3: The Popovers SHOULD be shown below the Filter Item or the "Add" Bulky Button.
	 *   ordering:
	 *     1: A Filter MUST be placed above the table it filters.
	 *   responsiveness:
	 *     1: >
	 *        On screens larger than medium size, there MUST be three Filter Items per row. On medium-sized screens
	 *        or below, only one Filter Item MUST be shown per row.
	 *     2: >
	 *        On screens larger than small size, the "Apply" and "Reset" Bulky Buttons MUST contain a glyph plus text.
	 *        On small-sized screens, these Bulky Buttons MUST contain only a glyph.
	 *   accessibility:
	 *     1: Filter Items MUST be accessible by keyboard by using the "Tab"-Key and clickable by using the "Return"-Key.
	 *
	 *
	 * ---
	 * @return  \ILIAS\UI\Component\Input\Container\Filter\Factory
	 */
	public function filter();
}
