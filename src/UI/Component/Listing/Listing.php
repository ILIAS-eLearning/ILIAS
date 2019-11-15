<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing;

/**
 */
interface Listing extends \ILIAS\UI\Component\Component
{
    /**
     * Sets the items to be listed
     *
     * @param array $items (Component|string)[]
     * @return Listing
     */
    public function withItems(array $items);

    /**
     * Gets the items to be listed
     *
     * @return array $items (Component|string)[]
     */
    public function getItems();
}
