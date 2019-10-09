<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Item;

/**
 * Common interface to item groups
 */
interface Group extends \ILIAS\UI\Component\Component
{
    /**
     * Gets the title of the group
     *
     * @return string
     */
    public function getTitle();

    /**
     * Gets item of the group
     *
     * @return \ILIAS\UI\Component\Item\Item[]
     */
    public function getItems();

    /**
     * Create a new appointment item with a set of actions to perform on it.
     *
     * @param \ILIAS\UI\Component\Dropdown\Standard $dropdown
     * @return Group
     */
    public function withActions(\ILIAS\UI\Component\Dropdown\Standard $dropdown);

    /**
     * Get the actions Dropdown of the group
     *
     * @return \ILIAS\UI\Component\Dropdown\Standard
     */
    public function getActions();
}
