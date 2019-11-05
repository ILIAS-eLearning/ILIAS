<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component as C;

class Standard extends Item implements C\Item\Standard
{
    /**
     * @var \ILIAS\Data\Color color
     */
    protected $color = null;
    /**
     * @var null|string|\ILIAS\UI\Component\Image\Image
     */
    protected $lead = null;

    /**
     * @inheritdoc
     */
    public function withColor(\ILIAS\Data\Color $color)
    {
        $clone        = clone $this;
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
        $clone       = clone $this;
        $clone->lead = $image;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLeadIcon(\ILIAS\UI\Component\Symbol\Icon\Icon $icon)
    {
        $clone       = clone $this;
        $clone->lead = $icon;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withLeadText($text)
    {
        $this->checkStringArg("lead_text", $text);
        $clone       = clone $this;
        $clone->lead = (string) $text;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withNoLead()
    {
        $clone       = clone $this;
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