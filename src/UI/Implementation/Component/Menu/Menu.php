<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component;
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
    public function getItems() : array
    {
        return $this->items;
    }

    /**
     * @param array	$items
     */
    protected function checkItemParameter(array $items)
    {
        $classes = [
            Sub::class,
            Component\Clickable::class,
            Component\Link\Link::class,
            Component\Divider\Horizontal::class
        ];
        $this->checkArgListElements("items", $items, $classes);
    }
}
