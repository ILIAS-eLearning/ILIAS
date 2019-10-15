<?php
declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Component\Menu as IMenu;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

/**
 * Level of Drilldown Control
 */
class Sub extends LabeledMenu implements IMenu\Sub
{
    use JavaScriptBindable;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @param Component\Clickable | string $label
     * @param array <Sub | Component\Clickable | Component\Divider\Horizontal> $items
     */
    public function __construct($label, array $items)
    {
        $this->checkLabelParameter($label);
        $this->checkItemParameter($items);
        $this->label = $label;
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function withInitiallyActive() : IMenu\Sub
    {
        $clone = clone $this;
        $clone->active = true;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isInitiallyActive() : bool
    {
        return $this->active;
    }
}
