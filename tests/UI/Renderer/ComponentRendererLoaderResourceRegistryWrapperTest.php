<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


class ComponentRendererLoaderResourceRegistryWrapperTest extends PHPUnit_Framework_TestCase
{
    public function test_forwards_from_underlying()
    {
        $underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->setMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ComponentRenderer::class)
            ->setMethods(["registerResources", "render"])
            ->getMock();
        $component = $this->getMockBuilder(\ILIAS\UI\Component\Component::class)->getMock();
        $context = ["a", "b"];
        $underlying
            ->expects($this->once())
            ->method("getRendererFor")
            ->with($component, $context)
            ->willReturn($renderer);

        $registry = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ResourceRegistry::class)
            ->getMock();

        $l = new \ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper($registry, $underlying);
        $r = $l->getRendererFor($component, $context);

        $this->assertSame($renderer, $r);
    }

    public function test_registerResources()
    {
        $underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->setMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $renderer = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ComponentRenderer::class)
            ->setMethods(["registerResources", "render"])
            ->getMock();
        $component = $this->getMockBuilder(\ILIAS\UI\Component\Component::class)->getMock();
        $underlying
            ->expects($this->once())
            ->method("getRendererFor")
            ->with($component, [])
            ->willReturn($renderer);

        $registry = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ResourceRegistry::class)
            ->getMock();

        $renderer
            ->expects($this->once())
            ->method("registerResources")
            ->with($registry);

        $l = new \ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper($registry, $underlying);
        $r = $l->getRendererFor($component, []);
    }

    public function test_passthrough_getRendererFactory()
    {
        $underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\Loader::class)
            ->setMethods(["getRendererFor", "getRendererFactoryFor"])
            ->getMock();

        $c1 = $this->createMock(\ILIAS\UI\Component\Component::class);

        $factory = "FACTORY";
        $underlying
            ->expects($this->exactly(1))
            ->method("getRendererFactoryFor")
            ->with($c1)
            ->willReturn($factory);

        $l = new \ILIAS\UI\Implementation\Render\LoaderCachingWrapper($underlying);

        $this->assertSame($factory, $l->getRendererFactoryFor($c1));
    }
}
