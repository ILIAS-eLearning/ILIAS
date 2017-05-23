<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


class ComponentRendererLoaderResourceRegistryWrapperTest extends PHPUnit_Framework_TestCase {
	public function test_forwards_from_underlying() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ComponentRenderer::class)
			->setMethods(["registerResources", "render"])
			->getMock();
		$component = $this->getMockBuilder(\ILIAS\UI\Component\Component::class)->getMock();
		$underlying
			->expects($this->once())
			->method("getRendererFor")
			->with($component)
			->willReturn($renderer);

		$registry = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ResourceRegistry::class)
			->getMock();

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderResourceRegistryWrapper($registry, $underlying);
		$r = $l->getRendererFor($component);

		$this->assertSame($renderer, $r);
	}

	public function test_caches() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ComponentRenderer::class)
			->setMethods(["registerResources", "render"])
			->getMock();
		$component = $this->getMockBuilder(\ILIAS\UI\Component\Component::class)->getMock();
		$underlying
			->expects($this->once())
			->method("getRendererFor")
			->with($component)
			->willReturn($renderer);

		$registry = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ResourceRegistry::class)
			->getMock();

		$renderer
			->expects($this->once())
			->method("registerResources")
			->with($registry);

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderResourceRegistryWrapper($registry, $underlying);
		$r = $l->getRendererFor($component);
	}
}
