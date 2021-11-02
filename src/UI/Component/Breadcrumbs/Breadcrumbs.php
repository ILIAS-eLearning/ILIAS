<?php declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Breadcrumbs;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Link\Standard;

/**
 * Interface for Breadcrumbs
 * @package ILIAS\UI\Component\Breadcrumbs
 */
interface Breadcrumbs extends Component
{
    /**
     * Get all crumbs.
     *
     * @return 	Standard[]
     */
    public function getItems() : array;

    /**
     * Append a crumb-entry to the bar.
     */
    public function withAppendedItem(Standard $crumb) : Breadcrumbs;
}
