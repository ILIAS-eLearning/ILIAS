<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;

class AdminConfirmedObjectiveTest extends \PHPUnit\Framework\TestCase {
	public function setUp() : void {
		$this->message = "This needs to be confirmed...";
		$this->o = new Setup\AdminConfirmedObjective($this->message);
	}

	public function testGetHash() {
		$this->assertIsString($this->o->getHash());
	}

	public function testHashIsDifferentForDifferentMessages() {
		$other = new Setup\AdminConfirmedObjective("");
		$this->assertNotEquals($this->o->getHash(), $other->getHash());
	}

	public function testGetLabel() {
		$this->assertIsString($this->o->getLabel());
	}

	public function testIsNotable() {
		$this->assertFalse($this->o->isNotable());
	}

	public function testGetPreconditions() {
		$env = $this->createMock(Setup\Environment::class);

		$pre = $this->o->getPreconditions($env);
		$this->assertEquals([], $pre);	
	}

	public function testAchieveWithConfirmation() {
		$env = $this->createMock(Setup\Environment::class);
		$confirmation_requester = $this->createMock(Setup\ConfirmationRequester::class);

		$env
			->expects($this->once())
			->method("getResource")
			->with(Setup\Environment::RESOURCE_CONFIRMATION_REQUESTER)
			->willReturn($confirmation_requester);

		$confirmation_requester
			->expects($this->once())
			->method("confirmOrDeny")
			->with($this->message)
			->willReturn(true);

		$res = $this->o->achieve($env);
		$this->assertSame($env, $res);
	}

	public function testAchieveWithDenial() {
		$this->expectException(Setup\UnachievableException::class);

		$env = $this->createMock(Setup\Environment::class);
		$confirmation_requester = $this->createMock(Setup\ConfirmationRequester::class);

		$env
			->expects($this->once())
			->method("getResource")
			->with(Setup\Environment::RESOURCE_CONFIRMATION_REQUESTER)
			->willReturn($confirmation_requester);

		$confirmation_requester
			->expects($this->once())
			->method("confirmOrDeny")
			->with($this->message)
			->willReturn(false);

		$res = $this->o->achieve($env);
	}
}
