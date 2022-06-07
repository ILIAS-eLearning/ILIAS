<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
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
