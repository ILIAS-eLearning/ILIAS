<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Item\Group;
use ILIAS\UI\Component\Dropdown;

/**
 * Interface Appointment
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Listing extends Component
{
    /**
     * Gets the title of the appointment listing
     */
    public function getTitle() : string;

    /**
     * Get item list
     *
     * @return Group[]
     */
    public function getItemGroups() : array;

    /**
     * Sets the action dropdown to be displayed on the right of the title
     */
    public function withActions(Dropdown\Standard $actions) : Listing;

    /**
     * Gets the action dropdown to be displayed on the right of the title
     */
    public function getActions() : ?Dropdown\Standard;
}
