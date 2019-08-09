<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Common interface to all items.
 */
class Group implements C\Item\Group
{
    use ComponentHelper;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var C\Item\Item[]
     */
    protected $items;

    /**
     * @var \ILIAS\UI\Component\Dropdown\Standard
     */
    protected $actions;

    /**
     * Group constructor.
     * @param $title
     * @param C\Item\Item[] $items
     */
    public function __construct($title, array $items)
    {
        $this->checkStringArg("title", $title);
        $this->title = $title;
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function withActions(\ILIAS\UI\Component\Dropdown\Standard $actions)
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions()
    {
        return $this->actions;
    }
}
