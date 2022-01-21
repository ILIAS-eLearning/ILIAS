<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item;
use ILIAS\UI\Component\Dropdown;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Common interface to all items.
 */
class Group implements Item\Group
{
    use ComponentHelper;

    protected string $title;

    /**
     * @var Item\Item[]
     */
    protected array $items;
    protected ?Dropdown\Standard $actions = null;

    /**
     * @param Item\Item[] $items
     */
    public function __construct(string $title, array $items)
    {
        $this->title = $title;
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function withActions(Dropdown\Standard $dropdown) : Item\Group
    {
        $clone = clone $this;
        $clone->actions = $dropdown;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions() : ?Dropdown\Standard
    {
        return $this->actions;
    }
}
