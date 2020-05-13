<?php

declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Link;

use ILIAS\UI\Component\Symbol;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * Bulky Link - a visually enriched link that looks like a button but behaves like a link.
 */
interface Bulky extends Link, JavaScriptBindable
{
    /**
     * Get the label of the link
     */
    public function getLabel() : string;

    /**
     * Get the Icon or Glyph the Link was created with.
     */
    public function getSymbol() : Symbol\Symbol;
}
