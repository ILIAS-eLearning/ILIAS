<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Render\LoaderHelper;
use ILIAS\UI\Component\Test\TestComponent;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;

require_once(__DIR__ . "/TestComponent.php");

class ComponentRendererLoaderHelperTest extends TestCase
{
    use LoaderHelper;

    public function test_getContextNames() : void
    {
        $c1 = new TestComponent("foo");
        $c2 = new Glyph("up", "up");
        $names = $this->getContextNames([$c1, $c2]);
        $expected = ["TestComponentTest", "GlyphGlyphSymbol"];
        $this->assertEquals($expected, $names);
    }
}
