<?php declare(strict_types=1);

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component\Item\Group;

/**
 * Interface Listing
 * @package ILIAS\UI\Component\Panel\Secondary
 */
interface Listing extends Secondary
{
    /**
     * Get item list
     *
     * @return Group[]
     */
    public function getItemGroups() : array;
}
