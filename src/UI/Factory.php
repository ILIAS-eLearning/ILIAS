<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 *
 * Consumers of the UI-Service must program against this interface and not
 * use any concrete implementations.
 */
interface Factory {
    /**
     * Description
     *  * Purpose: Counter inform users about the quantity of items indicated
     *    by a glyph.
     *  * Composition: Counters consist of a number and some background color
     *    and are placed one the 'end of the line' in reading direction of the
     *    the item they state the count for.
     *  * Effect: Counters convey information, they are not interactive.
     *  * Rival elements: none
     *
     * Rules:
     *  * A counter MUST only be used in combination with a glyph.
     *  * A counter MUST contain exactly one number greater than zero and no
     *    other characters.
     *
     * @return  \ILIAS\UI\Factory\Counter
     */
    public function counter();

    /**
     * @return  \ILIAS\UI\Factory\Glyph
     */
    public function glyph();
}