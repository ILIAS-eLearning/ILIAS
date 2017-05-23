<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */


class ComponentRendererLoaderCachingWrapper extends PHPUnit_Framework_TestCase {
	public function test_forwards_from_underlying() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer = new \stdClass();
		$component = $this->getMockBuilder(\ILIAS\UI\Component\Component::class)->getMock();
		$context = [new \ILIAS\UI\Component\Test\TestComponent("foo")];
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

	public function test_caching_respects_contexts() {
		$underlying = $this->getMockBuilder(\ILIAS\UI\Implementation\ComponentRendererLoader::class)
			->setMethods(["getRendererFor"])
			->getMock();

		$renderer1 = new \stdClass();
		$renderer2 = new \stdClass();
		$c1 = $this->getMockBuilder(\ILIAS\UI\Component\Component::class)->getMock();
		$c2 = new \ILIAS\UI\Component\Test\TestComponent("foo");
		$underlying
			->expects($this->exactly(2))
			->method("getRendererFor")
			->withConsecutive
				( [$c1, [] ]
				, [$c1, [$c2]])
			->will($this->onConsecutiveCalls($renderer1, $renderer2));

		$l = new \ILIAS\UI\Implementation\ComponentRendererLoaderCachingWrapper($underlying);
		$r1 = $l->getRendererFor($c1, []);
		$r2 = $l->getRendererFor($c1, [$c2]);
		$r3 = $l->getRendererFor($c1, [$c2]);
		$r4 = $l->getRendererFor($c1, []);

		$this->assertSame($renderer1, $r1);
		$this->assertSame($renderer2, $r2);
		$this->assertSame($renderer2, $r3);
		$this->assertSame($renderer1, $r4);
	}
}
