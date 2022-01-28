<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Chart;

use ILIAS\UI\Component\Component;

/**
 * Interface Scale Bars
 */
interface ScaleBar extends Component
{
    /**
     * Sets a key value pair as items for the list. Key is used as title and value is a boolean marking highlighted values.
     * @param array string => boolean Set of elements to be rendered, boolean should be true if highlighted
     */
    public function withItems(array $items) : ScaleBar;

    /**
     * Gets the key value pair as array. Key is used as title and value is a boolean marking highlighted values.
     * @return array $items string => boolean
     */
    public function getItems() : array;
}
