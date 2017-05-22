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
		$renderer_class = "MyRenderer";
		$underlying
			->expects($this->once())
			->method("getRendererFor")
			->with($renderer_class)
			->willReturn($renderer);

		$registry = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ResourceRegistry::class)
			->getMock();

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderResourceRegistryWrapper($registry, $underlying);
		$r = $l->getRendererFor($renderer_class);

		$this->assertSame($renderer, $r);
	}

	public function test_caches() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ComponentRenderer::class)
			->setMethods(["registerResources", "render"])
			->getMock();
		$renderer_class = "MyRenderer";
		$underlying
			->expects($this->once())
			->method("getRendererFor")
			->with($renderer_class)
			->willReturn($renderer);

		$registry = $this->getMockBuilder(\ILIAS\UI\Implementation\Render\ResourceRegistry::class)
			->getMock();

		$renderer
			->expects($this->once())
			->method("registerResources")
			->with($registry);

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderResourceRegistryWrapper($registry, $underlying);
		$r = $l->getRendererFor($renderer_class);
	}
}
