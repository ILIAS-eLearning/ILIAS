<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\Signal;

/**
 * A Button can be initially hidden; that means, that the button in not relayed
 * to the user in any way before an explicit user action.
 */
interface Hideable
{
    /**
     * Returns whether the button can be hidden or not.
     */
    public function isHideable() : bool;

    /**
     * Sets the hidden-flag to true.
     */
    public function withInitiallyHidden() : Hideable;

    /**
     * Returns whether the button is initially hidden or not.
     */
    public function isInitiallyHidden() : bool;

    /**
     * Signal that shows the Button when triggered.
     */
    public function getRevealSignal() : Signal;
}
