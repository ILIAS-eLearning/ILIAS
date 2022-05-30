<?php

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

require_once(__DIR__ . "/TestComponent.php");
require_once(__DIR__ . "/../Base.php");

use ILIAS\UI\Component\Test\TestComponent;
use ILIAS\UI\Implementation\Render\DecoratedRenderer;

class DecoratedRendererTest extends ILIAS_UI_TestBase
{
    public function test_render()
    {
        $c1 = new TestComponent("foo");
        $renderer = $this->getDecoratedRenderer($this->getDefaultRenderer());
        $html = $renderer->render($c1);
        $this->assertEquals("foo", $html);
    }

    public function test_render_async()
    {
        $c1 = new TestComponent("foo");
        $renderer = $this->getDecoratedRenderer($this->getDefaultRenderer());
        $html = $renderer->renderAsync($c1);
        $this->assertEquals("foo", $html);
    }

    public function test_render_with_manipulation()
    {
        $c1 = new TestComponent("foo");
        $renderer = $this->getDecoratedRenderer($this->getDefaultRenderer());
        $renderer->manipulate();
        $html = $renderer->render($c1);
        $this->assertEquals("This content was manipulated", $html);
    }
}
