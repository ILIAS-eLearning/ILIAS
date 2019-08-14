<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Report;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Component;

/**
 * Class Standard
 * @package ILIAS\UI\Implementation\Component\Listing\Report
 */
class Standard extends Report implements C\Listing\Report\Standard
{
    /**
     * @var Component|null
     */
    protected $divider;

    /**
     * @inheritdoc
     */
    public function withDivider(Component $divider)
    {
        $clone = clone $this;
        $clone->items = $this->items;
        $clone->divider = $divider;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function hasDivider()
    {
        return $this->divider instanceof Component;
    }

    /**
     * @inheritdoc
     */
    public function getDivider()
    {
        return $this->divider;
    }
}
