<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;

use ILIAS\UI\Component\Button\Button;
use ILIAS\UI\Component\Component;

/**
 * This is how the factory for UI elements looks.
 */
interface Factory
{

    /**
     * ---
     * description:
     *   purpose: >
     *      Mode View Controls enable the switching between different aspects of some data. The different modes are mutually
     *      exclusive and can therefore not be activated at once.
     *   composition: >
     *      Mode View Controls are composed of Buttons switching between active and inactive states.
     *   effect: >
     *      Clicking on an inactive Button turns this button active and all other inactive. Clicking on an active button
     *      has no effect.
     *
     * rules:
     *   usage:
     *      1: Exactly one Button MUST always be active.
     *   accessibility:
     *      1: The HTML container enclosing the buttons of the Mode View Control MUST cary the role-attribute "group".
     *      2: The HTML container enclosing the buttons of the Mode View Control MUST set an aria-label describing the element. Eg. "Mode View Control"
     *      3: The Buttons of the Mode View Control MUST set an aria-label clearly describing what the button shows if clicked. E.g. "List View", "Month View", ...
     *      4: The currently active Button must be labeled by setting aria-checked to "true".
     *
     * ---
     * @param    array $labelled_actions Set of labelled actions (string|string)[]. The label of the action is used as key, the action itself as value.
     *          The first of the actions will be activated by default.
     * @param string $aria_label Defines the functionality.
     *
     * @return \ILIAS\UI\Component\ViewControl\Mode
     */
    public function mode($labelled_actions, $aria_label);

    /**
     * ---
     * description:
     *   purpose: >
     *      Section View Controls enable the switching between different sections of some data. Examples are subsequent
     *      days/weeks/months in a calendar or entries in a blog.
     *   composition: >
     *      Section View Controls are composed of three Buttons. The Button on the left caries a Back Glyph, the Button
     *      in the middle is either a Default- or Split Button labeling the data displayed below and the Button on the right carries
     *      a next Glyph.
     *   effect: >
     *      Clicking on the Buttons left or right changes the selection of the displayed data by a fixed interval. Clicking
     *      the Button in the middle opens the sections hinted by the label of the button (e.g. "Today").
     *
     * ---
     *
     * @param   \ILIAS\UI\Component\Button\Button $previous_action Button to be placed in the left.
     * @param   \ILIAS\UI\Component\Button\Button|\ILIAS\UI\Component\Button\Month $button Button to be placed in the middle (Month Button or Default Button).
     * @param   \ILIAS\UI\Component\Button\Button $next_action Button to be placed in the right.
     *
     * @return \ILIAS\UI\Component\ViewControl\Section
     */
    public function section(Button $previous_action, \ILIAS\UI\Component\Component $button, Button $next_action);

    /**
     * ---
     * description:
     *   purpose: >
     *      The sortation view control enables users to change the order in which
     *      some data is presented.
     *      This control applies to all sorts of _structured_ data, like tables and lists.
     *   composition: >
     *      Sortation uses a Dropdown to display a collection of shy-buttons.
     *   effect: >
     *      A click on an option will change the ordering of the associated data-list
     *      by calling a page with a parameter according to the selected option or triggering a signal.
     *      The label displayed in the dropdown will be set to the selected sorting.
     *
     * rules:
     *   usage:
     *      1: A Sortation MUST NOT be used standalone.
     *      2: Sortations MUST BE visually close to the list or table their operation will have effect upon.
     *      3: There SHOULD NOT be more than one Sortation per view.
     *   accessibility:
     *      1: Sortation MUST be operable via keyboard only.
     *
     * ---
     * @param array<string,string>  $options 	a dictionary with value=>title
     *
     * @return \ILIAS\UI\Component\ViewControl\Sortation
     */
    public function sortation(array $options);

    /**
     * ---
     * description:
     *   purpose: >
     *      Pagination allows structured data being displayed in chunks by
     *      limiting the number of entries shown. It provides the user with
     *      controls to leaf through the chunks of entries.
     *   composition: >
     *      Pagination is a collection of shy-buttons to access distinct chunks
     *      of data, framed by next/back glyphs.
     *      When used with the "DropdownAt" option, a dropdown is rendered if
     *      the number of chunks exceeds the option's value.
     *   effect: >
     *      A click on an chunk-option will change the offset of the displayed data-list,
     *      thus displaying the respective chunk of entries.
     *      The active option is rendered as an unavailable shy-button.
     *      Clicking the next/back-glyphs, the previous (respectively: the next)
     *      chunk of entries is being displayed.
     *      If a previous/next chunk is not available, the glyph is
     *      rendered unavailable.
     *      If the pagination is used with a maximum of chunk-options to be shown,
     *      both first and last options are always displayed.
     *
     * rules:
     *   usage:
     *      1: A Pagination MUST only be used for structured data, like tables and lists.
     *      2: A Pagination MUST NOT be used standalone.
     *      3: Paginations MUST be visually close to the list or table their operation will have effect upon. They MAY be placed directly above and/or below the list.
     *   accessibility:
     *      1: Pagination MUST be operable via keyboard only.
     *
     * ---
     * @param
     *
     * @return \ILIAS\UI\Component\ViewControl\Pagination
     */
    public function pagination();
}
