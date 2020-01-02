<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/TestComponent.php");
require_once(__DIR__ . "/../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\DefaultRenderer;
use \ILIAS\UI\Implementation\Glyph\Renderer as GlyphRenderer;

class DefaultRendererTest extends ILIAS_UI_TestBase
{
    public function test_getRenderer_successfully()
    {
        // There should be a renderer for Glyph...
        $dr = $this->getDefaultRenderer();
        $r = $dr->_getRendererFor(new \ILIAS\UI\Implementation\Component\Glyph\Glyph("up", "up"));
        $this->assertInstanceOf(\ILIAS\UI\Implementation\Render\ComponentRenderer::class, $r);
    }

    public function test_getRenderer_caching()
    {
        $dr = $this->getDefaultRenderer();
        $r1 = $dr->_getRendererFor(new \ILIAS\UI\Implementation\Component\Glyph\Glyph("up", "up"));
        $r2 = $dr->_getRendererFor(new \ILIAS\UI\Implementation\Component\Glyph\Glyph("up", "up"));
        $this->assertTrue($r1 === $r2, "Instances not equal");
    }

    public function getResourceRegistry()
    {
        $this->resource_registry = parent::getResourceRegistry();
        return $this->resource_registry;
    }

    public function test_invokesRegistry()
    {
        $dr = $this->getDefaultRenderer();
        $component = new \ILIAS\UI\Component\Test\TestComponent("foo");

        $dr->render($component);

        $this->assertEquals(array("test.js"), $this->resource_registry->resources);
    }

    public function test_withAdditionalContext_clones()
    {
        $dr = $this->getDefaultRenderer();
        $component = new \ILIAS\UI\Component\Test\TestComponent("foo");
        $dr2 = $dr->withAdditionalContext($component);
        $this->assertNotSame($dr, $dr2);
    }

    public function test_getContexts()
    {
        $dr = $this->getDefaultRenderer();
        $c1 = new \ILIAS\UI\Component\Test\TestComponent("foo");
        $c2 = new \ILIAS\UI\Component\Test\TestComponent("bar");
        $dr2 = $dr
            ->withAdditionalContext($c1)
            ->withAdditionalContext($c2);
        $this->assertEquals([], $dr->_getContexts());
        $this->assertEquals([$c1, $c2], $dr2->_getContexts());
    }

    public function test_passesContextsToComponentRendererLoader()
    {
        $loader = $this
            ->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->setMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer = new TestDefaultRenderer($loader);

        $c1 = new \ILIAS\UI\Component\Test\TestComponent("foo");
        $c2 = new \ILIAS\UI\Component\Test\TestComponent("bar");

        $renderer = $renderer
            ->withAdditionalContext($c1)
            ->withAdditionalContext($c2);

        $loader
            ->expects($this->once())
            ->method("getRendererFor")
            ->with($c1, [$c1, $c2]);

        $renderer->_getRendererFor($c1);
    }

    public function test_render()
    {
        $c1 = new \ILIAS\UI\Component\Test\TestComponent("foo");
        $renderer = $this->getDefaultRenderer();
        $html = $renderer->render($c1);
        $this->assertEquals("foo", $html);
    }

    public function test_render_async_no_js()
    {
        $c1 = new \ILIAS\UI\Component\Test\TestComponent("foo");
        $renderer = $this->getDefaultRenderer(
            new \ILIAS\UI\Implementation\Render\ilJavaScriptBinding(
                $this->getTemplateFactory()->getTemplate(false, false, false)
            )
        );
        $html = $renderer->renderAsync($c1);
        $this->assertEquals("foo", $html);
    }

    public function test_render_async_with_js()
    {
        $c1 = new \ILIAS\UI\Component\Test\JSTestComponent("foo");
        $renderer = $this->getDefaultRenderer(
            new \ILIAS\UI\Implementation\Render\ilJavaScriptBinding($this->getTemplateFactory()->getTemplate(false, false, false))
        );
        $html = $renderer->renderAsync($c1);
        $this->assertEquals('foo<script data-replace-marker="script">id:foo.id content:foo</script>', $html);
    }

    public function test_render_async_with_js_twice()
    {
        $c1 = new \ILIAS\UI\Component\Test\TestComponent("foo");
        $c2 = new \ILIAS\UI\Component\Test\JSTestComponent("foo");
        $renderer = $this->getDefaultRenderer(
            new \ILIAS\UI\Implementation\Render\ilJavaScriptBinding($this->getTemplateFactory()->getTemplate(false, false, false))
        );
        $html = $renderer->renderAsync($c2);
        $this->assertEquals('foo<script data-replace-marker="script">id:foo.id content:foo</script>', $html);
        $html = $renderer->renderAsync($c1);
        $this->assertEquals("foo", $html);
        $html = $renderer->renderAsync($c2);
        $this->assertEquals('foo<script data-replace-marker="script">id:foo.id content:foo</script>', $html);
    }

    public function test_render_async_array()
    {
        $c1 = new \ILIAS\UI\Component\Test\TestComponent("foo");

        $renderer = $this->getDefaultRenderer(
            new \ILIAS\UI\Implementation\Render\ilJavaScriptBinding($this->getTemplateFactory()->getTemplate(false, false, false))
        );
        $html = $renderer->renderAsync([$c1,$c1]);
        $this->assertEquals('foofoo', $html);
    }
}
