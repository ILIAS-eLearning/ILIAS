<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;
/**
 * This is how the factory for UI elements looks.
 */
interface Factory {

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
	 *      1: The Buttons of the Mode View Control MUST cary the role-attribute "group".
	 *      2: The Buttons of the Mode View Control MUST set an aria-label clearly describing it's functionality.
	 *      3: The currently active Button must be labeled by setting aria-checked to "true".
	 *
	 * ---
	 * @param    array $labelled_actions Set of labelled actions (string|string)[]. The label of the action is used as key, the action itself as value.
	 *          The first of the actions will be activated by default.
	 *
	 * @return \ILIAS\UI\Component\ViewControl\Mode
	 */
	public function mode($labelled_actions);

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
	 * @param   string $previous_action action to be executed by clicking on the left Button.
	 * @param   \ILIAS\UI\Component\Component $button Button to be placed in the middle (Split Button or Default Button).
	 * @param   string $next_action action to be executed by clicking on the left Button.
	 *
	 * @return \ILIAS\UI\Component\ViewControl\Section
	 */
	public function section($previous_action, $button, $next_action);

}
