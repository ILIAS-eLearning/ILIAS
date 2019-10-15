<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

/**
 * Interface Listing
 * @package ILIAS\UI\Component\Panel\Secondary
 */
interface Listing extends Secondary
{

    /**
     * Get item list
     *
     * @return \ILIAS\UI\Component\Item\Group[]
     */
    public function getItemGroups() : array;
}
