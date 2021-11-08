<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Render\Loader;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper;
use ILIAS\UI\Implementation\Render\LoaderCachingWrapper;
use ILIAS\UI\Implementation\Render\RendererFactory;

class ComponentRendererLoaderResourceRegistryWrapperTest extends TestCase
{
    public function test_forwards_from_underlying() : void
    {
        $underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->onlyMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ComponentRenderer::class)
            ->onlyMethods(["registerResources", "render"])
            ->getMock();
        $component = $this->getMockBuilder(Component::class)->getMock();
        $context = ["a", "b"];
        $underlying
            ->expects($this->once())
            ->method("getRendererFor")
            ->with($component, $context)
            ->willReturn($renderer);

        $registry = $this->getMockBuilder(ResourceRegistry::class)
            ->getMock();

        $l = new LoaderResourceRegistryWrapper($registry, $underlying);
        $r = $l->getRendererFor($component, $context);

        $this->assertSame($renderer, $r);
    }

    public function test_registerResources() : void
    {
        $underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->onlyMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ComponentRenderer::class)
            ->onlyMethods(["registerResources", "render"])
            ->getMock();
        $component = $this->getMockBuilder(Component::class)->getMock();
        $underlying
            ->expects($this->once())
            ->method("getRendererFor")
            ->with($component, [])
            ->willReturn($renderer);

        $registry = $this->getMockBuilder(ResourceRegistry::class)
            ->getMock();

        $renderer
            ->expects($this->once())
            ->method("registerResources")
            ->with($registry);

        $l = new LoaderResourceRegistryWrapper($registry, $underlying);
        $l->getRendererFor($component, []);
    }

    public function test_passthrough_getRendererFactory() : void
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
