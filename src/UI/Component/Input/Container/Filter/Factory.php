<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Filter;

use ILIAS\UI\Component\Signal;

/**
 * This is how a factory for filters looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Possible properties, which only Standard Filters have, should be mentioned here.
	 *
	 * rules:
	 *   usage:
	 *     1: Possible rules, which only Standard Filters have, should be mentioned here.
	 *
	 * ---
	 *
	 * @param    string|Signal    $toggle_action_on
	 * @param    string|Signal    $toggle_action_off
	 * @param    string|Signal    $expand_action
	 * @param    string|Signal    $collapse_action
	 * @param    string|Signal    $apply_action
	 * @param    string|Signal    $reset_action
	 * @param    array<mixed,\ILIAS\UI\Component\Input\Input>    $inputs
	 * @param    array<bool>    $is_input_rendered
	 * @param    bool    $is_activated
	 * @param    bool    $is_expanded
	 *
	 * @return    \ILIAS\UI\Component\Input\Container\Filter\Standard
	 */
	public function standard($toggle_action_on, $toggle_action_off, $expand_action, $collapse_action, $apply_action, $reset_action, array $inputs, array $is_input_rendered, $is_activated = false, $is_expanded = false);

	/*
	"Other types of container might use other mechanisms for data submission. A filter
	e.g. will likely be commiting its content via query parameters in the URL to make
	the results of the query cachable and maintain HTTP-semantics. Another type of
	form might submit its contents asynchronously."
	*/
}