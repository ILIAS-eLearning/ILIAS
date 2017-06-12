<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Link;

/**
 * Link factory
 */
interface Factory {
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       A standard link is a link with a text label as content of the link.
	 *   composition: >
	 *       The standard link uses the default link color as text color an no
	 *       background.
	 *   effect: >
	 *       Hovering a link should indicate its interactivity by switching to
	 *       a link:hover class. Clicking a link can have a variety of effects
	 *       that may lead to new network requests or Javascript actions.
	 *
	 * rules:
	 *   usage:
	 *       1: >
	 *          Standard links MUST be used if there is no good reason using
	 *          another instance. Containers that accept links as subcomponents
	 *          should define the usage of the link, e.g. switching to another
	 *          screen, performing an action or starting a workflow.
	 * ---
	 * @param	string		$label
	 * @param	string		$action
	 * @return  \ILIAS\UI\Component\Link\Standard
	 */
	public function standard($label, $action);
}
