<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Element;

/**
 * This describes how a glyph could be modified during construction of UI.
 */
interface Glyph {
    /**
     * Add a counter to the glyph.
     *
     * @param   Counter
     * @throws  \InvalidArgumentException   If an according counter is already
     *                                      attached to the glyph.
     * @return  Glyph
     */
    public function addCounter(Counter $counter);
}