<?php

require_once(__DIR__."/GlyphTest.php");

/**
 * This tests the actual implementation of the factory.
 */
class GlyphImplementationTest extends GlyphTest {
    public function getFactoryInstance() {
        return new \ILIAS\UI\Internal\Glyph\FactoryImpl();
    }

    public function getCounterFactoryInstance() {
        return new \ILIAS\UI\Internal\Counter\FactoryImpl();
    }
}
