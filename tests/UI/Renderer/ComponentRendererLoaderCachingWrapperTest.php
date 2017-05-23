<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


class ComponentRendererLoaderCachingWrapper extends PHPUnit_Framework_TestCase {
	public function test_forwards_from_underlying() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer = new \stdClass();
		$component = $this->getMockBuilder(\ILIAS\UI\Component\Component::class)->getMock();
		$context = ["a", "b"];
		$underlying
			->expects($this->once())
			->method("getRendererFor")
			->with($component, $context)
			->willReturn($renderer);

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderCachingWrapper($underlying);
		$r = $l->getRendererFor($component, $context);

		$this->assertSame($renderer, $r);
	}

	public function test_caches() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer = new \stdClass();
		$component = $this->getMockBuilder(\ILIAS\UI\Component\Component::class)->getMock();
		$underlying
			->expects($this->once())
			->method("getRendererFor")
			->with($component, [])
			->willReturn($renderer);

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderCachingWrapper($underlying);
		$r1 = $l->getRendererFor($component, []);
		$r2 = $l->getRendererFor($component, []);

		$this->assertSame($renderer, $r1);
		$this->assertSame($renderer, $r2);
	}
}
