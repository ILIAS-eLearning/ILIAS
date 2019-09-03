<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;

class NullObjectiveTest extends \PHPUnit\Framework\TestCase {
	public function setUp() : void {
		$this->o = new Setup\NullObjective();
	}

	public function testGetHash() {
		$this->assertIsString($this->o->getHash());
	}

	public function testGetLabel() {
		$this->assertEquals("Nothing to do.", $this->o->getLabel());
	}

	public function testIsNotable() {
		$this->assertFalse($this->o->isNotable());
	}

	public function testGetPreconditions() {
		$env = $this->createMock(Setup\Environment::class);

		$pre = $this->o->getPreconditions($env);
		$this->assertEquals([], $pre);	
	}


	public function testAchieve() {
		$env = $this->createMock(Setup\Environment::class);

		$res = $this->o->achieve($env);
		$this->assertSame($env, $res);
	}
}
