<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Divider;

use ILIAS\UI\Component\Component;

/**
 * Horizontal Divider
 */
interface Horizontal extends Component
{
    /**
     * Get the label of the divider
     *
     * @return	string
     */
    public function getLabel();

    /**
     * Get a divider like this, but with another label
     *
     * @param	string	$label
     * @return	Horizontal
     */
    public function withLabel($label);
}
