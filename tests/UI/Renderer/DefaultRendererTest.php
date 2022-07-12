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
 
require_once(__DIR__ . "/TestComponent.php");
require_once(__DIR__ . "/../Base.php");

use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Component\Test\TestComponent;
use ILIAS\UI\Implementation\Render\Loader;
use ILIAS\UI\Implementation\Render\ilJavaScriptBinding;
use ILIAS\UI\Component\Test\JSTestComponent;
use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\DefaultRenderer;
use \ILIAS\UI\Renderer;

class DefaultRendererTest extends ILIAS_UI_TestBase
{
    public function test_getRenderer_successfully() : void
    {
        // There should be a renderer for Glyph...
        $dr = $this->getDefaultRenderer();
        $r = $dr->_getRendererFor(new Glyph("up", "up"));
        $this->assertInstanceOf(ComponentRenderer::class, $r);
    }

    public function test_getRenderer_caching() : void
    {
        $dr = $this->getDefaultRenderer();
        $r1 = $dr->_getRendererFor(new Glyph("up", "up"));
        $r2 = $dr->_getRendererFor(new Glyph("up", "up"));
        $this->assertTrue($r1 === $r2, "Instances not equal");
    }

    public function getResourceRegistry() : LoggingRegistry
    {
        $this->resource_registry = parent::getResourceRegistry();
        return $this->resource_registry;
    }

    public function test_invokesRegistry() : void
    {
        $dr = $this->getDefaultRenderer();
        $component = new TestComponent("foo");

        $dr->render($component);

        $this->assertEquals(array("test.js"), $this->resource_registry->resources);
    }

    public function test_withAdditionalContext_clones() : void
    {
        $dr = $this->getDefaultRenderer();
        $component = new TestComponent("foo");
        $dr2 = $dr->withAdditionalContext($component);
        $this->assertNotSame($dr, $dr2);
    }

    public function test_getContexts() : void
    {
        $dr = $this->getDefaultRenderer();
        $c1 = new TestComponent("foo");
        $c2 = new TestComponent("bar");
        $dr2 = $dr
            ->withAdditionalContext($c1)
            ->withAdditionalContext($c2);
        $this->assertEquals([], $dr->_getContexts());
        $this->assertEquals([$c1, $c2], $dr2->_getContexts());
    }

    public function test_passesContextsToComponentRendererLoader() : void
    {
        $loader = $this
            ->getMockBuilder(Loader::class)
            ->onlyMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer = new TestDefaultRenderer($loader);

        $c1 = new TestComponent("foo");
        $c2 = new TestComponent("bar");

        $renderer = $renderer
            ->withAdditionalContext($c1)
            ->withAdditionalContext($c2);

        $loader
            ->expects($this->once())
            ->method("getRendererFor")
            ->with($c1, [$c1, $c2]);

        $renderer->_getRendererFor($c1);
    }

    public function test_render() : void
    {
        $c1 = new TestComponent("foo");
        $renderer = $this->getDefaultRenderer();
        $html = $renderer->render($c1);
        $this->assertEquals("foo", $html);
    }

    public function test_render_async_no_js() : void
    {
        $c1 = new TestComponent("foo");
        $renderer = $this->getDefaultRenderer(
            new ilJavaScriptBinding(
                $this->getTemplateFactory()->getTemplate("tpl.main.html", false, false)
            )
        );
        $html = $renderer->renderAsync($c1);
        $this->assertEquals("foo", $html);
    }

    public function test_render_async_with_js() : void
    {
        $c1 = new JSTestComponent("foo");
        $renderer = $this->getDefaultRenderer(
            new ilJavaScriptBinding($this->getTemplateFactory()->getTemplate("tpl.main.html", false, false))
        );
        $html = $renderer->renderAsync($c1);
        $this->assertEquals('foo<script data-replace-marker="script">id:foo.id content:foo</script>', $html);
    }

    public function test_render_async_with_js_twice() : void
    {
        $c1 = new TestComponent("foo");
        $c2 = new JSTestComponent("foo");
        $renderer = $this->getDefaultRenderer(
            new ilJavaScriptBinding($this->getTemplateFactory()->getTemplate("tpl.main.html", false, false))
        );
        $html = $renderer->renderAsync($c2);
        $this->assertEquals('foo<script data-replace-marker="script">id:foo.id content:foo</script>', $html);
        $html = $renderer->renderAsync($c1);
        $this->assertEquals("foo", $html);
        $html = $renderer->renderAsync($c2);
        $this->assertEquals('foo<script data-replace-marker="script">id:foo.id content:foo</script>', $html);
    }

    public function test_render_async_array() : void
    {
        $c1 = new TestComponent("foo");

        $renderer = $this->getDefaultRenderer(
            new ilJavaScriptBinding($this->getTemplateFactory()->getTemplate("tpl.main.html", false, false))
        );
        $html = $renderer->renderAsync([$c1,$c1]);
        $this->assertEquals('foofoo', $html);
    }

    /**
     * @dataProvider render_type
     */
    public function test_passes_self_as_root_if_no_root_exist($render_type)
    {
        $this->component_renderer = $this->createMock(ComponentRenderer::class);
        $component = $this->createMock(C\Component::class);

        $renderer = new class($this) extends DefaultRenderer {
            public function __construct($self)
            {
                $this->self = $self;
            }

            protected function getRendererFor(ILIAS\UI\Component\Component $component) : ComponentRenderer
            {
                return $this->self->component_renderer;
            }

            protected function getJSCodeForAsyncRenderingFor(C\Component $component)
            {
                return "";
            }
        };

        $this->component_renderer->expects($this->once())
            ->method("render")
            ->with($component, $renderer);

        $renderer->$render_type($component);
    }

    /**
     * @dataProvider render_type
     */
    public function test_passes_other_on_as_root($render_type)
    {
        $this->component_renderer = $this->createMock(ComponentRenderer::class);
        $component = $this->createMock(C\Component::class);
        $root = $this->createMock(Renderer::class);

        $renderer = new class($this) extends DefaultRenderer {
            public function __construct($self)
            {
                $this->self = $self;
            }

            protected function getRendererFor(ILIAS\UI\Component\Component $component) : ComponentRenderer
            {
                return $this->self->component_renderer;
            }

            protected function getJSCodeForAsyncRenderingFor(C\Component $component)
            {
                return "";
            }
        };

        $this->component_renderer->expects($this->once())
            ->method("render")
            ->with($component, $root);

        $renderer->$render_type($component, $root);
    }

    public function render_type()
    {
        return [
            ["render"],
            ["renderAsync"]
        ];
    }

    public function test_component_list_uses_root_to_render()
    {
        $component = $this->createMock(C\Component::class);
        $root = $this->createMock(Renderer::class);

        $renderer = new class($this) extends DefaultRenderer {
            public function __construct($self)
            {
                $this->self = $self;
            }
        };

        $root->expects($this->exactly(2))
            ->method("render")
            ->with($component)
            ->willReturn(".");

        $res = $renderer->render([$component, $component], $root);
        $this->assertEquals("..", $res);
    }
}
