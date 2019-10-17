<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/TestComponent.php");

class ComponentRendererLoaderHelperTest extends PHPUnit_Framework_TestCase
{
    use \ILIAS\UI\Implementation\Render\LoaderHelper;

    public function test_getContextNames()
    {
        $c1 = new \ILIAS\UI\Component\Test\TestComponent("foo");
        $c2 = new \ILIAS\UI\Implementation\Component\Glyph\Glyph("up", "up");
        $names = $this->getContextNames([$c1, $c2]);
        $expected = ["TestComponentTest", "GlyphGlyph"];
        $this->assertEquals($expected, $names);
    }
}
