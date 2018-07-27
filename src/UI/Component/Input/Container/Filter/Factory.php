<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Filter;

/**
 * This is how a factory for filters looks like.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Standard Filters...
	 *   composition: >
	 *      Standard Filters...
	 *   effect: >
	 *      The users manipulates...
	 *
	 * rules:
	 *   usage:
	 *     1: Standard Filters...
	 *     2: Standard Filters...
	 *   composition:
	 *     1: Each Filter...
	 *     2: >
	 *         Standard Filters...
	 *     3: >
	 *        Wording...
	 *     4: >
	 *        On top...
	 *     5: >
	 *        In some...
	 *
	 * ---
	 *
	 * @param    string $post_url
	 * @param    array<mixed,\ILIAS\UI\Component\Input\Input>    $inputs
	 *
	 * @return    \ILIAS\UI\Component\Input\Container\Filter\Standard
	 */
	public function standard($post_url, array $inputs);
}