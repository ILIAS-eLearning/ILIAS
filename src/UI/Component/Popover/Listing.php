<?php declare(strict_types=1);

namespace ILIAS\UI\Component\Popover;

use ILIAS\UI\Component\Item\Item;

/**
 * A listing popover renders multiple items as a list.
 *
 * @package ILIAS\UI\Component\Popover
 */
interface Listing extends Popover
{
    /**
     * Get the list items of this popover.
     *
     * @return Item[]
     */
    public function getItems() : array;
}
