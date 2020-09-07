<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Dropdown;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes commonalities between all types of Dropdowns
 */
interface Dropdown extends Component, JavaScriptBindable, Clickable, Hoverable
{

    /**
     * Get the items of the Dropdown.
     *
     * @return	array<\ILIAS\UI\Component\Button\Shy|\ILIAS\UI\Component\Divider\Horizontal>
     */
    public function getItems();

    /**
     * Get the label of the Dropdown.
     *
     * @return	string
     */
    public function getLabel();

    /**
     * Get the aria-label of the Dropdown.
     *
     * @return	string
     */
    public function getAriaLabel();

    /**
     * Get a Dropdown like this, but with an additional/replaced label.
     *
     * @param	string	$label
     * @return	Dropdown
     */
    public function withLabel($label);

    /**
     * Get a Dropdown like this, but with an additional/replaced aria-label.
     *
     * @param	string	$label
     * @return	Dropdown
     */
    public function withAriaLabel($label);
}
