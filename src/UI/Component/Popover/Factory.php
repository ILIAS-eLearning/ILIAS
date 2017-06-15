<?php

namespace ILIAS\UI\Component\Popover;

use \ILIAS\UI\Component\Component as Component;
/**
 * Factory to create different types of Popovers.
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Standard Popovers are used to display other components.
	 *   composition: >
	 *      The content of a Standard Popover displays the components together with an optional title.
	 * rivals: >
	 *    Listing Popovers are used to display lists.
	 * rules:
	 *   usage:
	 *      1: Standard Popovers MUST NOT be used to render lists, use a Listing Popover for this purpose.
	 *      2: Standard Popovers SHOULD NOT contain complex or large components.
	 * ---
	 * @param Component|Component[] $content
	 * @return \ILIAS\UI\Component\Popover\Standard
	 */
	public function standard($content);

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Listing Popovers are used to display list items.
	 *   composition: >
	 *      The content of a Listing Popover displays the list together with an optional title.
	 * rivals: >
	 *   Standard Popovers display other components than lists.
	 * rules:
	 *   usage:
	 *      1: Listing Popovers MUST be used if one needs to display lists inside a Popover.
	 * ---
	 * @param Component[] $items
	 * @return \ILIAS\UI\Component\Popover\Listing
	 */
	public function listing($items);

}
