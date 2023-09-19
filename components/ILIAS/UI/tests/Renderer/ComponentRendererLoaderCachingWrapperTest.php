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
use ILIAS\UI\Implementation\Render\Loader;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Test\TestComponent;
use ILIAS\UI\Implementation\Render\LoaderCachingWrapper;
use ILIAS\UI\Implementation\Render\RendererFactory;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

class ComponentRendererLoaderCachingWrapperTest extends TestCase
{
    public function test_forwards_from_underlying(): void
    {
        $underlying = $this->getMockBuilder(Loader::class)
            ->onlyMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer = $this->createMock(ComponentRenderer::class);
        $component = $this->getMockBuilder(Component::class)->getMock();
        $context = [new TestComponent("foo")];
        $underlying
            ->expects($this->once())
            ->method("getRendererFor")
            ->with($component, $context)
            ->willReturn($renderer);

        $l = new LoaderCachingWrapper($underlying);
        $r = $l->getRendererFor($component, $context);

        $this->assertSame($renderer, $r);
    }

    public function test_caches(): void
    {
        $underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->onlyMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer = $this->createMock(ComponentRenderer::class);
        $component = $this->getMockBuilder(Component::class)->getMock();
        $underlying
            ->expects($this->once())
            ->method("getRendererFor")
            ->with($component, [])
            ->willReturn($renderer);

        $l = new LoaderCachingWrapper($underlying);
        $r1 = $l->getRendererFor($component, []);
        $r2 = $l->getRendererFor($component, []);

        $this->assertSame($renderer, $r1);
        $this->assertSame($renderer, $r2);
    }

    public function test_caching_respects_contexts(): void
    {
        $underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->onlyMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer1 = $this->createMock(ComponentRenderer::class);
        $renderer2 = $this->createMock(ComponentRenderer::class);
        $c1 = $this->getMockBuilder(Component::class)->getMock();
        $c2 = new TestComponent("foo");
        $underlying
            ->expects($this->exactly(2))
            ->method("getRendererFor")
            ->withConsecutive([$c1, [] ], [$c1, [$c2]])
            ->will($this->onConsecutiveCalls($renderer1, $renderer2));

        $l = new LoaderCachingWrapper($underlying);
        $r1 = $l->getRendererFor($c1, []);
        $r2 = $l->getRendererFor($c1, [$c2]);
        $r3 = $l->getRendererFor($c1, [$c2]);
        $r4 = $l->getRendererFor($c1, []);

        $this->assertSame($renderer1, $r1);
        $this->assertSame($renderer2, $r2);
        $this->assertSame($renderer2, $r3);
        $this->assertSame($renderer1, $r4);
    }

    public function test_passthrough_getRendererFactory(): void
    {
        $underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->onlyMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $c1 = $this->createMock(Component::class);

        $factory = $this->createMock(RendererFactory::class);
        $underlying
            ->expects($this->exactly(1))
            ->method("getRendererFactoryFor")
            ->with($c1)
            ->willReturn($factory);

        $l = new LoaderCachingWrapper($underlying);

        $this->assertSame($factory, $l->getRendererFactoryFor($c1));
    }
}
