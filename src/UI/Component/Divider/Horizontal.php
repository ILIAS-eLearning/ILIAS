<?php declare(strict_types=1);

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
     */
    public function getLabel() : ?string;

    /**
     * Get a divider like this, but with another label
     */
    public function withLabel(string $label) : Horizontal;
}
