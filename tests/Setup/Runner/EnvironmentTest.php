<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\Runner;

use ILIAS\Setup\Runner;

class EnvironmentTest extends \PHPUnit\Framework\TestCase {
	public function setUp() : void {
		$this->environment = new Runner\Environment([
			"foo" => "FOO",
			"bar" => "BAR"
		]);
	}

	public function testGetResource() {
		$this->assertEquals("FOO", $this->environment->getResource("foo"));
		$this->assertEquals("BAR", $this->environment->getResource("bar"));
		$this->assertNull($this->environment->getResource("baz"));
	}

	public function testSetResource() {
		$this->environment->setResource("baz", "BAZ");

		$this->assertEquals("FOO", $this->environment->getResource("foo"));
		$this->assertEquals("BAR", $this->environment->getResource("bar"));
		$this->assertEquals("BAZ", $this->environment->getResource("baz"));
	}

	public function testSetResourceRejectsDuplicates() {
		$this->expectException(\RuntimeException::class);

		$this->environment->setResource("baz", "BAZ");
		$this->environment->setResource("baz", "BAZ");
	}	
}
