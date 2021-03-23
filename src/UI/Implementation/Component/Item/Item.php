<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Common interface to all items.
 */
abstract class Item implements C\Item\Item
{
    use ComponentHelper;
    /**
     * @var string|\ILIAS\UI\Component\Button\Shy|\ILIAS\UI\Component\Link\Link
     */
    protected $title;

    /**
     * @var string
     */
    protected $desc;

    /**
     * @var array
     */
    protected $props;

    /**
     * @var \ILIAS\UI\Component\Dropdown\Standard
     */
    protected $actions;

    /**
     * @var null|string|\ILIAS\UI\Component\Image\Image
     */
    protected $lead = null;

    /**
     * Item constructor.
     * @param \ILIAS\UI\Component\Button\Shy|\ILIAS\UI\Component\Link\Standard|string $title
     */
    public function __construct($title)
    {
        if (!$title instanceof \ILIAS\UI\Component\Button\Shy &&
            !$title instanceof \ILIAS\UI\Component\Link\Link) {
            $this->checkStringArg("title", $title);
        }
        $this->title = $title;
        $this->props = [];
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
    public function withDescription(string $desc) : C\Item\Item
    {
        $this->checkStringArg("description", $desc);
        $clone = clone $this;
        $clone->desc = $desc;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->desc;
    }

    /**
     * @inheritdoc
     */
    public function withProperties(array $props)
    {
        $clone = clone $this;
        $clone->props = $props;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getProperties()
    {
        return $this->props;
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
