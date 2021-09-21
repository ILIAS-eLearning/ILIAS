<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

/**
 * Level of Drilldown Control
 */
class Sub extends Menu implements IMenu\Sub
{
    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @param string $label
     * @param array <Sub | Component\Clickable | Component\Divider\Horizontal> $items
     */
    public function __construct(string $label, array $items)
    {
        $this->checkItemParameter($items);
        $this->label = $label;
        $this->items = $items;
    }
}
