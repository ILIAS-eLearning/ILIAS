<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Menu;

use \ILIAS\UI\Component\Component;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes a Submenu, i.e. an item for a menu providing further items.
 */
interface Sub extends LabeledMenu, JavaScriptBindable
{
	/**
	 * Configure this Submenu to be active when the menu is loaded.
	 */
	public function withInitiallyActive(): Sub;

	/**
	 * Is this initially active?
	 */
	public function isInitiallyActive(): bool;
}
