<?php
declare(strict_types=1);

namespace ILIAS\UI\Component\Tree\Node;

use ILIAS\UI\Component\Icon\Icon;

/**
 * Nodes factory
 */
interface Factory
{
	/**
	 * ---
	 * description:
	 *   purpose: >
	 *     The Simple Node is a very basic entry for a Tree.
	 *   composition: >
	 *     It consists of a string-label and an optional Icon.
	 *   effect: >
	 *     The Simple Node can be configured with an URL to load
	 *     data asynchronously. In this case, before loading there is always
	 *     an Expand Glyph in front of the Node.
	 *     If there are no further levels, the Expand Glyph will disappear
	 *     after loading.
	 *     Furthermore, SimpleNode implements Clickable and can be configured to
	 *     trigger an action.
	 *
	 * rules:
	 *   usage:
	 *      1: >
	 *        A Simple Node SHOULD be used when there is no need to relay
	 *        further information for the user to choose. This is the case
	 *        for most occurences where repository-items are shown.
	 *
	 * ---
	 * @param string $label
	 * @param \ILIAS\UI\Component\Icon\Icon|null $icon
	 *
	 * @return \ILIAS\UI\Component\Tree\Node\Simple
	 */
	public function simple(string $label, Icon $icon=null): Simple;

}
