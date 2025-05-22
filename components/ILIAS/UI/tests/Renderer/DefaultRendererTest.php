<?php

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

declare(strict_types=1);

require_once(__DIR__ . "/TestComponent.php");
require_once(__DIR__ . "/../Base.php");

use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Component\Test\TestComponent;
use ILIAS\UI\Implementation\Render\Loader;
use ILIAS\UI\Implementation\Render\ilJavaScriptBinding;
use ILIAS\UI\Component\Test\JSTestComponent;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\DefaultRenderer;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render;
use ILIAS\UI\Implementation\Render\JavaScriptBinding;

class DefaultRendererTest extends ILIAS_UI_TestBase
{
    protected LoggingRegistry $resource_registry;
    public ComponentRenderer $component_renderer;

    public function testGetRendererSuccessfully(): void
    {
        // There should be a renderer for Glyph...
        $dr = $this->getDefaultRenderer();
        $r = $dr->_getRendererFor(new Glyph("up", "up"));
        $this->assertInstanceOf(ComponentRenderer::class, $r);
    }

    public function testGetRendererCaching(): void
    {
        $dr = $this->getDefaultRenderer();
        $r1 = $dr->_getRendererFor(new Glyph("up", "up"));
        $r2 = $dr->_getRendererFor(new Glyph("up", "up"));
        $this->assertTrue($r1 === $r2, "Instances not equal");
    }

    public function getResourceRegistry(): LoggingRegistry
    {
        $this->resource_registry = parent::getResourceRegistry();
        return $this->resource_registry;
    }

    public function testInvokesRegistry(): void
    {
        $dr = $this->getDefaultRenderer();
        $component = new TestComponent("foo");

        $dr->render($component);

        $this->assertEquals(array("test.js"), $this->resource_registry->resources);
    }

    /**
     * Simulates the rendering chain and tests if the contexts are properly stacked.
     * DefaultRenderer -> ConcreteRenderer -> DefaultRenderer -> ConcreteRenderer ...
     */
    public function testRenderingChainAndContextStack(): void
    {
        $component1 = new TestComponent("component1");
        $component2 = new TestComponent("component2");
        $component3 = new TestComponent("component3");
        $glue = '.';

        $component_renderer = $this->createMock(ComponentRenderer::class);
        $component_renderer->method('render')->willReturnCallback(
            static function (TestComponent $component, DefaultRenderer $renderer) use (
                $component1,
                $component2,
                $component3,
                $glue
            ) {
                return match ($component->text) {
                    $component1->text => $component1->text . $glue . $renderer->render($component2),
                    $component2->text => $component2->text . $glue . $renderer->render($component3),
                    $component3->text => $component3->text,
                };
            }
        );

        $renderer = new class (
            $this->createMock(Render\Loader::class),
            $this->createMock(JavaScriptBinding::class),
            $this->createMock(\ILIAS\Language\Language::class),
            $component_renderer,
        ) extends DefaultRenderer {
            public array $context_history = [];

            public function __construct(
                Render\Loader $component_renderer_loader,
                JavaScriptBinding $java_script_binding,
                \ILIAS\Language\Language $language,
                protected ComponentRenderer $component_renderer
            ) {
                parent::__construct($component_renderer_loader, $java_script_binding, $language);
            }

            protected function getRendererFor(Component $component): ComponentRenderer
            {
                return $this->component_renderer;
            }

            protected function pushContext(Component $component): void
            {
                parent::pushContext($component);
                $this->context_history[] = $this->getContexts();
            }

            protected function popContext(): void
            {
                parent::popContext();
                $this->context_history[] = $this->getContexts();
            }
        };

        $expected_context_history = [
            [$component1],
            [$component1, $component2],
            [$component1, $component2, $component3],
            [$component1, $component2],
            [$component1],
            []
        ];

        $expected_html =
            $component1->text . $glue .
            $component2->text . $glue .
            $component3->text;

        $html = $renderer->render($component1);

        $this->assertEquals($expected_context_history, $renderer->context_history);
        $this->assertEquals($expected_html, $html);
    }

    public function testRender(): void
    {
        $c1 = new TestComponent("foo");
        $renderer = $this->getDefaultRenderer();
        $html = $renderer->render($c1);
        $this->assertEquals("foo", $html);
    }

    public function testRenderAsyncNoJs(): void
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

    public function testRenderAsyncWithJs(): void
    {
        $c1 = new JSTestComponent("foo");
        $renderer = $this->getDefaultRenderer(
            new ilJavaScriptBinding($this->getTemplateFactory()->getTemplate("tpl.main.html", false, false))
        );
        $html = $renderer->renderAsync($c1);
        $this->assertEquals('foo<script data-replace-marker="script">id:foo.id content:foo</script>', $html);
    }

    public function testRenderAsyncWithJsTwice(): void
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

    public function testRenderAsyncArray(): void
    {
        $c1 = new TestComponent("foo");

        $renderer = $this->getDefaultRenderer(
            new ilJavaScriptBinding($this->getTemplateFactory()->getTemplate("tpl.main.html", false, false))
        );
        $html = $renderer->renderAsync([$c1,$c1]);
        $this->assertEquals('foofoo', $html);
    }

    /**
     * @dataProvider getRenderType
     */
    public function testPassesSelfAsRootIfNoRootExist($render_type)
    {
        $this->component_renderer = $this->createMock(ComponentRenderer::class);
        $component = $this->createMock(C\Component::class);

        $loader = $this->createMock(Loader::class);
        $loader->method('getRendererFor')->willReturn($this->component_renderer);

        $renderer = new TestDefaultRenderer($loader, $this->getJavaScriptBinding(), $this->getLanguage());

        $this->component_renderer->expects($this->once())
            ->method("render")
            ->with($component, $renderer);

        $renderer->$render_type($component);
    }

    /**
     * @dataProvider getRenderType
     */
    public function testPassesOtherOnAsRoot($render_type)
    {
        $this->component_renderer = $this->createMock(ComponentRenderer::class);
        $component = $this->createMock(C\Component::class);
        $root = $this->createMock(Renderer::class);

        $loader = $this->createMock(Loader::class);
        $loader->method('getRendererFor')->willReturn($this->component_renderer);

        $renderer = new TestDefaultRenderer($loader, $this->getJavaScriptBinding(), $this->getLanguage());

        $this->component_renderer->expects($this->once())
            ->method("render")
            ->with($component, $root);

        $renderer->$render_type($component, $root);
    }

    public static function getRenderType()
    {
        return [
            ["render"],
            ["renderAsync"]
        ];
    }

    public function testComponentListUsesRootToRender()
    {
        $component = $this->createMock(C\Component::class);
        $root = $this->createMock(Renderer::class);

        $renderer = $this->getDefaultRenderer();

        $root->expects($this->exactly(2))
            ->method("render")
            ->with($component)
            ->willReturn(".");

        $res = $renderer->render([$component, $component], $root);
        $this->assertEquals("..", $res);
    }
}
