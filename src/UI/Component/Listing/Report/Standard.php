<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\Report;

use ILIAS\UI\Component\Component;

/**
 * Interface Standard
 * @package ILIAS\UI\Component\Listing\Report
 */
interface Standard extends Report
{
    /**
     * Adds a divider to be rendered between the rows of label => item.
     * Use a \ILIAS\UI\Component\Divider\Horizontal for example.
     *
     * @param Component
     * @return Standard
     */
    public function withDivider(Component $component);

    /**
     * Returns the fact whether a divider component was added or not.
     *
     * @return bool
     */
    public function hasDivider();

    /**
     * Returns the divider component if one was added. Returns null otherwise.
     *
     * @return Component|null
     */
    public function getDivider();
}
