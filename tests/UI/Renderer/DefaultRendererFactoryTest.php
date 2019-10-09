<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class DefaultRendererFactoryForTest extends \ILIAS\UI\Implementation\Render\DefaultRendererFactory
{
    public function __construct()
    {
    }

    public function _getRendererNameFor($component)
    {
        return $this->getRendererNameFor($component);
    }
}

class DefaultRendererFactoryTest extends TestCase
{
    public function test_getRendererNameFor()
    {
        $f = new DefaultRendererFactoryForTest;

        $renderer_class = $f->_getRendererNameFor(new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph("up", "up"));
        $expected = \ILIAS\UI\Implementation\Component\Symbol\Glyph\Renderer::class;
        $this->assertEquals($expected, $renderer_class);
    }
}
