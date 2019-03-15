<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Tree\Node;

/**
 * This describes a very basic Tree Node.
 */
interface Simple extends Node, AsyncNode
{
	/**
	 * Get the icon for this Node.
	 * @return Icon|null
	 */
	public function getIcon();
}
