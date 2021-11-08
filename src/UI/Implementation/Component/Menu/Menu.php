<?php declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Basic Menu Control
 */
abstract class Menu implements IMenu\Menu
{
    use ComponentHelper;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Component\Component[]
     */
    protected array $items = [];

    /**
     * @inheritdoc
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }

    protected function checkItemParameter(array $items) : void
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
