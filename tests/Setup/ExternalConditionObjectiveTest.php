<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;

class ExternalConditionObjectiveTest extends \PHPUnit\Framework\TestCase {
	public function setUp() : void {
		$this->label_t = "condition_true";
		$this->t = new Setup\ExternalConditionObjective(
			$this->label_t,
			function (Setup\Environment $e) { return true; }
		);
		$this->label_f = "condition_false";
		$this->f = new Setup\ExternalConditionObjective(
			$this->label_f,
			function (Setup\Environment $e) { return false; }
		);
	}

	public function testGetHash() {
		$this->assertIsString($this->t->getHash());
	}

	public function testHashIsDifferentForDifferentMessages() {
		$this->assertNotEquals($this->t->getHash(), $this->f->getHash());
	}

	public function testGetLabel() {
		$this->assertIsString($this->f->getLabel());
		$this->assertEquals($this->label_f, $this->f->getLabel());
		$this->assertEquals($this->label_t, $this->t->getLabel());
	}

	public function testIsNotable() {
		$this->assertTrue($this->f->isNotable());
	}

	public function testGetPreconditions() {
		$env = $this->createMock(Setup\Environment::class);

		$pre = $this->f->getPreconditions($env);
		$this->assertEquals([], $pre);	
	}

	public function testAchieveFalse() {
		$this->expectException(Setup\UnachievableException::class);
		$env = $this->createMock(Setup\Environment::class);
		$res = $this->f->achieve($env);
	}


	public function testAchieveTrue() {
		$env = $this->createMock(Setup\Environment::class);
		$res = $this->t->achieve($env);
		$this->assertEquals($env, $res);
	}
}
