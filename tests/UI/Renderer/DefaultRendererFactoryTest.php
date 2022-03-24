<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Renderer;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;

class DefaultRendererFactoryForTest extends DefaultRendererFactory
{
    public function __construct()
    {
    }

    public function _getRendererNameFor(\ILIAS\UI\Component\Component $component) : string
    {
        return $this->getRendererNameFor($component);
    }
}

class DefaultRendererFactoryTest extends TestCase
{
    public function test_getRendererNameFor() : void
    {
        $f = new DefaultRendererFactoryForTest;

        $renderer_class = $f->_getRendererNameFor(new Glyph("up", "up"));
        $expected = Renderer::class;
        $this->assertEquals($expected, $renderer_class);
    }
}
