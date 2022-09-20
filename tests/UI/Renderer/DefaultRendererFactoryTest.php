<?php

declare(strict_types=1);

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
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Renderer;
use ILIAS\UI\Implementation\Render\DefaultRendererFactory;

class DefaultRendererFactoryForTest extends DefaultRendererFactory
{
    public function __construct()
    {
    }

    public function _getRendererNameFor(\ILIAS\UI\Component\Component $component): string
    {
        return $this->getRendererNameFor($component);
    }
}

class DefaultRendererFactoryTest extends TestCase
{
    public function test_getRendererNameFor(): void
    {
        $f = new DefaultRendererFactoryForTest();

        $renderer_class = $f->_getRendererNameFor(new Glyph("up", "up"));
        $expected = Renderer::class;
        $this->assertEquals($expected, $renderer_class);
    }
}
