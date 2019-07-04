<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Menu;

use ILIAS\UI\Component\Component;

/**
 * This describes a Menu Control with a label
 */
interface LabeledMenu extends Menu
{
	/**
	 * Get the label for this menu.
	 * @return Component\Clickable | string
	 */
	public function getLabel();

	/**
	 * @param Component\Clickable | string 	$label
	 */
	public function withLabel($label): LabeledMenu;
}
