<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button\Split;

/**
 * This is how a factory for split buttons looks like.
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Standard Split Buttons is used to group a list of actions with one labeled as default.
	 *   composition: >
	 *       The Standard Split Button shows the options in the dropdown as simple list.
	 *   effect: >
	 *       Selecting one of the actions in the dropdown directly fires it.
	 *
	 * rules:
	 *   interaction:
	 *       1: >
	 *          Selecting one of the actions in the dropdown of the Split Button MUST directly fire this action.
	 * ---
	 * @param    array $labelled_actions Set of labelled actions (string|string)[]. The label of the action is used as key, the action itself as value.
	 *          The first of the actions will be used as default action, directly visible.
	 *
	 * @return  \ILIAS\UI\Component\Button\Split\Standard
	 */
	public function standard($labelled_actions);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       The Month Split Button enables to select a specific month to fire some action (probably a change of view).
	 *   composition: >
	 *       The Month Split Button is composed of a Button showing the default month directly (probably the
	 *       month currently rendered by some view). The Dropdown contains an interface enabling the selection of a month from
	 *       the future or the past.
	 *   effect: >
	 *      Selecting a month from the dropdown directly fires the according action (e.g. switching the view to the selected month).
	 *
	 * rules:
	 *   interaction:
	 *       1: >
	 *          Selecting a month from the dropdown MUST directly fire the according action.
	 *
	 * ---
	 * @param    string $default Label of the month directly shown as default.
	 * @return  \ILIAS\UI\Component\Button\Split\Month
	 */
	public function month($default);
}

