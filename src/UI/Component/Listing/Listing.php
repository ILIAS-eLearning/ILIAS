<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing;

use ILIAS\UI\Component\Component;

interface Listing extends Component
{
    /**
     * Sets the items to be listed
     *
     * @param array $items (Component|string)[]
     */
    public function withItems(array $items) : Listing;

    /**
     * Gets the items to be listed
     *
     * @return array $items (Component|string)[]
     */
    public function getItems() : array;
}
