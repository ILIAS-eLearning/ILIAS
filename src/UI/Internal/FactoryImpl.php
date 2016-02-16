<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Internal;

class FactoryImpl implements \ILIAS\UI\Factory {
    /**
     * @return \ILIAS\UI\Factory\Counter
     */
    public function counter() {
        return new \ILIAS\UI\Internal\Counter\FactoryImpl();
    }


    /**
     * @return \ILIAS\UI\Factory\Glyph
     */
    public function glyph() {
        return new \ILIAS\UI\Internal\Glyph\FactoryImpl();
    }
}