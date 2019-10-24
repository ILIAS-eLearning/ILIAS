<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Icon;

/**
 * This describes the behavior of an custom icon.
 */
interface Custom extends Icon
{

    /**
     * Return the path to the icon's image
     *
     * @return string
     */
    public function getIconPath();
}
