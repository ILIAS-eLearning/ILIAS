<?php

require_once(__DIR__ . "/TestComponent.php");
require_once(__DIR__ . "/../Base.php");

use \ILIAS\UI\Component as C;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

class PluginRendererTest extends ILIAS_UI_TestBase
{
    public function test_getRenderer_successfully()
    {
        $plugin_renderer = $this->getPluginRenderer();
        $renderer = $plugin_renderer->_getRendererFor(new C\Test\TestComponent('test'));
        $this->assertInstanceOf(\ILIAS\UI\Implementation\Render\ComponentRenderer::class, $renderer);
    }

    public function test_getRenderer_caching()
    {
        $plugin_renderer = $this->getPluginRenderer();
        $renderer1 = $plugin_renderer->_getRendererFor(new C\Test\TestComponent('test'));
        $renderer2 = $plugin_renderer->_getRendererFor(new C\Test\TestComponent('test'));
        $this->assertTrue($renderer1 === $renderer2, "Instances not equal");
    }

    public function test_getRenderer_withAppend()
    {
        $plugin_renderer = $this->getPluginRendererWithAppend();
        $renderer = $plugin_renderer->_getRendererFor(new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph("up", "up"));
        $this->assertInstanceOf(\ILIAS\UI\Implementation\Render\ComponentRenderer::class, $renderer);
        $renderer = $plugin_renderer->_getRendererFor(new C\Test\TestComponent('test'));
        $this->assertInstanceOf(\ILIAS\UI\Implementation\Render\WrappingRenderer::class, $renderer);
    }

    public function test_getRenderer_withPrepend()
    {
        $plugin_renderer = $this->getPluginRendererWithPrepend();
        $renderer = $plugin_renderer->_getRendererFor(new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph("up", "up"));
        $this->assertInstanceOf(\ILIAS\UI\Implementation\Render\ComponentRenderer::class, $renderer);
        $renderer = $plugin_renderer->_getRendererFor(new C\Test\TestComponent('test'));
        $this->assertInstanceOf(\ILIAS\UI\Implementation\Render\WrappingRenderer::class, $renderer);
    }

    public function test_getRenderer_withReplace()
    {
        $plugin_renderer = $this->getPluginRendererWithReplace();
        $renderer = $plugin_renderer->_getRendererFor(new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph("up", "up"));
        $this->assertInstanceOf(\ILIAS\UI\Implementation\Render\ComponentRenderer::class, $renderer);
        $renderer = $plugin_renderer->_getRendererFor(new C\Test\TestComponent('test'));
        $this->assertInstanceOf(C\Test\Renderer::class, $renderer);
    }

    public function test_render()
    {
        $component = new C\Test\TestComponent('test');
        $plugin_renderer = $this->getPluginRenderer();
        $html = $plugin_renderer->_getRendererFor($component)->render($component, $this->getDefaultRenderer());
        $this->assertEquals("test", $html);
    }

    public function test_render_with_append()
    {
        $component = new C\Test\TestComponent('test');
        $plugin_renderer = $this->getPluginRendererWithAppend();
        $html = $plugin_renderer->_getRendererFor($component)->render($component, $this->getDefaultRenderer());
        $this->assertEquals("testappend", $html);
    }

    public function test_render_with_prepend()
    {
        $component = new C\Test\TestComponent('test');
        $plugin_renderer = $this->getPluginRendererWithPrepend();
        $html = $plugin_renderer->_getRendererFor($component)->render($component, $this->getDefaultRenderer());
        $this->assertEquals("prependtest", $html);
    }

    public function test_render_with_replace()
    {
        $component = new C\Test\TestComponent('test');
        $plugin_renderer = $this->getPluginRendererWithReplace();
        $html = $plugin_renderer->_getRendererFor($component)->render($component, $this->getDefaultRenderer());
        $this->assertEquals("replace", $html);
    }
}

