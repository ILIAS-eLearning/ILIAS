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
     * @description
     *  * Purpose: Counter inform users about the quantity of items indicated
     *    by a glyph.
     *  * ...
     *
     * @return  \ILIAS\UI\Factory\Counter
     */
    public function counter();

    /**
     * @description
     *  ...
     *
     * @return  \ILIAS\UI\Factory\Glyph
     */
    public function glyph();
}