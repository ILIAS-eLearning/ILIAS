<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

/**
 * Interface Appointment
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Listing extends \ILIAS\UI\Component\Component
{
    /**
     * Gets the title of the appointment listing
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get item list
     *
     * @return \ILIAS\UI\Component\Item\Group[]
     */
    public function getItemGroups();

    /**
     * Sets the action drop down to be displayed on the right of the title
     * @param \ILIAS\UI\Component\Dropdown\Standard $actions
     * @return Listing
     */
    public function withActions(\ILIAS\UI\Component\Dropdown\Standard $actions);

    /**
     * Gets the action drop down to be displayed on the right of the title
     * @return \ILIAS\UI\Component\Dropdown\Standard|null
     */
    public function getActions();
}
