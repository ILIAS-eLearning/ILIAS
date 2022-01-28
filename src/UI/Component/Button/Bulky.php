<?php declare(strict_types=1);

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\Symbol\Symbol;

/**
 * This describes a bulky button.
 */
interface Bulky extends Button
{
    /**
     * Get the icon or glyph the button was created with.
     */
    public function getIconOrGlyph() : Symbol;
}
