<?php declare(strict_types=1);
/* Copyright (c) 2021 Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component\Item\Shy as IShy;
use ILIAS\UI\Component\Button\Close;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as IJavaScriptBindable;
use ILIAS\UI\Component\Symbol\Icon\Icon;

class Shy extends Item implements IShy, IJavaScriptBindable
{
    use JavaScriptBindable;

    protected ?Icon $lead_icon = null;
    protected ?Close $close = null;

    /**
     * @inheritdoc
     */
    public function __construct(string $title)
    {
        parent::__construct($title);
    }

    /**
     * @inheritdoc
     */
    public function withClose(Close $close) : IShy
    {
        $clone = clone $this;
        $clone->close = $close;
        return $clone;
    }

    public function getClose() : ?Close
    {
        return $this->close;
    }

    /**
     * @inheritdoc
     */
    public function withLeadIcon(Icon $lead) : IShy
    {
        $clone = clone $this;
        $clone->lead_icon = $lead;
        return $clone;
    }

    public function getLeadIcon() : ?Icon
    {
        return $this->lead_icon;
    }
}
