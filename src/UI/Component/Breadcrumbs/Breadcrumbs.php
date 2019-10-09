<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Breadcrumbs;

/**
 * Interface for Breadcrumbs
 * @package ILIAS\UI\Component\Breadcrumbs
 */
interface Breadcrumbs extends \ILIAS\UI\Component\Component
{

    /**
     * Get all crumbs.
     *
     * @return 	\ILIAS\UI\Component\Link\Standard[]
     */
    public function getItems();

    /**
     * Append an crumb-entry to the bar.
     *
     * @param 	\ILIAS\UI\Component\Link\Standard 	$crumb
     * @return 	Breadcrumbs
     */
    public function withAppendedItem($crumb);
}
