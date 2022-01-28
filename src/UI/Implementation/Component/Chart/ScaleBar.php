<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Chart;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class ScaleBar
 * @package ILIAS\UI\Implementation\Component\Listing\Descriptive
 */
class ScaleBar implements C\Chart\ScaleBar
{
    use ComponentHelper;

    protected array $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function withItems(array $items) : C\Chart\ScaleBar
    {
        $clone = clone $this;
        $clone->items = $items;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
