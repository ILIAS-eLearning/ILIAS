<?php declare(strict_types=1);

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Dropdown;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Divider\Horizontal;
use ILIAS\UI\Component\Link\Standard;

/**
 * This describes commonalities between all types of Dropdowns
 */
interface Dropdown extends Component, JavaScriptBindable, Clickable, Hoverable
{

    /**
     * Get the items of the Dropdown.
     * @return	array<Shy|Horizontal|Standard>
     */
    public function getItems() : array;

    /**
     * Get the label of the Dropdown.
     */
    public function getLabel() : ?string;

    /**
     * Get the aria-label of the Dropdown.
     */
    public function getAriaLabel() : ?string;

    /**
     * Get a Dropdown like this, but with an additional/replaced label.
     */
    public function withLabel(string $label) : Dropdown;

    /**
     * Get a Dropdown like this, but with an additional/replaced aria-label.
     */
    public function withAriaLabel(string $label) : Dropdown;
}
