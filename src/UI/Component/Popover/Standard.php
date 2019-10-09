<?php

namespace ILIAS\UI\Component\Popover;

use ILIAS\UI\Component\Component;

/**
 * A standard popover renders any other component as its content.
 *
 * @package ILIAS\UI\Component\Popover
 */
interface Standard extends Popover
{

    /**
     * Get the components representing the content of the popover.
     *
     * @return Component[]
     */
    public function getContent();
}
