<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch>, Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Listing;

use ILIAS\UI\Component\Item;
use ILIAS\UI\Component\Panel\Listing\Group;

/**
 * Interface Factory
 * @package ILIAS\UI\Component\Panel\Listing
 */
interface Factory
{

    /**
     * ---
     * description:
     *   purpose: >
     *       Standard item lists present lists of items with similar presentation.
     *       All items are passed by using Item Groups.
     *   composition: >
     *      This Listing is composed of title and a set of Item Groups. Additionally an
     *      optional dropdown to select the number/types of items
     *      to be shown at the top of the Listing.
     * ---
     * @param string $title Title of the Listing
     * @param \ILIAS\UI\Component\Item\Group[] $item_groups Item groups
     * @return \ILIAS\UI\Component\Panel\Listing\Standard
     */
    public function standard($title, $item_groups);
}
