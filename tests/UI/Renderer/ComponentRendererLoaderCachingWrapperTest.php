<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


class ComponentRendererLoaderCachingWrapper extends PHPUnit_Framework_TestCase {
	public function test_forwards_from_underlying() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer = new \stdClass();
		$renderer_class = "MyRenderer";
		$underlying
			->expects($this->once())
			->method("getRendererFor")
			->with($renderer_class)
			->willReturn($renderer);

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderCachingWrapper($underlying);
		$r = $l->getRendererFor($renderer_class);

		$this->assertSame($renderer, $r);
	}

	public function test_caches() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer = new \stdClass();
		$renderer_class = "MyRenderer";
		$underlying
			->expects($this->once())
			->method("getRendererFor")
			->with($renderer_class)
			->willReturn($renderer);

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderCachingWrapper($underlying);
		$r1 = $l->getRendererFor($renderer_class);
		$r2 = $l->getRendererFor($renderer_class);

		$this->assertSame($renderer, $r1);
		$this->assertSame($renderer, $r2);
	}
}
