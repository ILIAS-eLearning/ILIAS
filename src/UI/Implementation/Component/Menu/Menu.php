<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;

/**
 * Basic Menu Control
 */
abstract class Menu implements IMenu\Menu
{
	/**
	 * @var Component[]
	 */
	protected $items = [];

	/**
	 * @inheritdoc
	 */
	public function getItems(): array
	{
		return $this->items;
	}
}