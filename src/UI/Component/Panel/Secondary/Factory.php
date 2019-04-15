<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component as C;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Panel\Secondary
 */
interface Factory {

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *       Secondary Listing Panel present lists of items with similar presentation.
	 *       All items are passed by using Item Groups.
	 *   composition: >
	 *      This Listing is composed of title and a set of Item Groups. Additionally an
	 *      optional dropdown to select the number/types of items
	 *      to be shown at the top of the Listing.
	 * ---
	 * @param string $title
	 * @param \ILIAS\UI\Component\Item\Group[] $item_groups Item groups
	 * @return C\Panel\Secondary\Listing
	 */
	public function listing(string $title, array $item_groups): C\Panel\Secondary\Listing;

	/**
	 * ---
	 * description:
	 *   purpose: >
	 *      Secondary Legacy Panel present content from a Legacy component.
	 *      A good example of when use Secondary Legacy Panel is the current mini calendar block.
	 *   composition: >
	 *      Secondary Legacy Panel is composed of title and a Legacy component.
	 * ---
	 * @param string $title
	 * @param \ILIAS\UI\Component\Legacy\Legacy $legacy
	 * @return C\Panel\Secondary\Legacy
	 */
	public function legacy(string $title, C\Legacy\Legacy $legacy): C\Panel\Secondary\Legacy;

}
