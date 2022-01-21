<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Dropdown\Standard;

/**
 * Common interface to item groups
 */
interface Group extends Component
{
    /**
     * Gets the title of the group
     */
    public function getTitle() : string;

    /**
     * Gets item of the group
     *
     * @return Item[]
     */
    public function getItems() : array;

    /**
     * Create a new appointment item with a set of actions to perform on it.
     */
    public function withActions(Standard $dropdown) : Group;

    /**
     * Get the actions Dropdown of the group
     */
    public function getActions() : ?Standard;
}
