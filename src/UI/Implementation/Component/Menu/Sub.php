<?php declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;

/**
 * Level of Drilldown Control
 */
class Sub extends Menu implements IMenu\Sub
{
    protected bool $active = false;

    /**
     * @param array <Sub|Component\Clickable|Component\Divider\Horizontal> $items
     */
    public function __construct(string $label, array $items)
    {
        $this->checkItemParameter($items);
        $this->label = $label;
        $this->items = $items;
    }
}
