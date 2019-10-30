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
     * @var \ILIAS\Data\Color color
     */
    protected $color = null;

    /**
     * @var string|\ILIAS\UI\Component\Button\Shy
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

    public function __construct($title)
    {
        if (!$title instanceof \ILIAS\UI\Component\Button\Shy) {
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
    public function withDescription($desc)
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

    /**
     * @inheritdoc
     */
    public function withColor(\ILIAS\Data\Color $color)
    {
        $clone = clone $this;
        $clone->color = $color;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @inheritdoc
     */
    public function withLeadImage(\ILIAS\UI\Component\Image\Image $image)
    {
        $clone = clone $this;
        $clone->lead = $image;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLeadIcon(\ILIAS\UI\Component\Icon\Icon $icon)
    {
        $clone = clone $this;
        $clone->lead = $icon;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLeadText($text)
    {
        $this->checkStringArg("lead_text", $text);
        $clone = clone $this;
        $clone->lead = (string) $text;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withNoLead()
    {
        $clone = clone $this;
        $clone->lead = null;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getLead()
    {
        return $this->lead;
    }
}
