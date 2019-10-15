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

	public function testAlreadyAchieved() {
		$env = $this->createMock(Setup\Environment::class);
		$confirmation_requester = $this->createMock(Setup\ConfirmationRequester::class);
		$achievement_tracker = $this->createMock(Setup\AchievementTracker::class);

		$env
			->method("getResource")
			->will($this->returnValueMap([
				[Setup\Environment::RESOURCE_CONFIRMATION_REQUESTER, $confirmation_requester],
				[Setup\Environment::RESOURCE_ACHIEVEMENT_TRACKER, $achievement_tracker]
			]));

		$confirmation_requester
			->expects($this->never())
			->method("confirmOrDeny");

		$achievement_tracker
			->expects($this->once())
			->method("isAchieved")
			->with($this->o)
			->willReturn(true);

		$achievement_tracker
			->expects($this->never())
			->method("trackAchievementOf");

		$res = $this->o->achieve($env);
		$this->assertSame($env, $res);
	}

	public function testAchieveWithConfirmation() {
		$env = $this->createMock(Setup\Environment::class);
		$confirmation_requester = $this->createMock(Setup\ConfirmationRequester::class);
		$achievement_tracker = $this->createMock(Setup\AchievementTracker::class);

		$env
			->method("getResource")
			->will($this->returnValueMap([
				[Setup\Environment::RESOURCE_CONFIRMATION_REQUESTER, $confirmation_requester],
				[Setup\Environment::RESOURCE_ACHIEVEMENT_TRACKER, $achievement_tracker]
			]));

		$confirmation_requester
			->expects($this->once())
			->method("confirmOrDeny")
			->with($this->message)
			->willReturn(true);

		$achievement_tracker
			->expects($this->once())
			->method("isAchieved")
			->with($this->o)
			->willReturn(false);

		$achievement_tracker
			->expects($this->once())
			->method("trackAchievementOf")
			->with($this->o);

		$res = $this->o->achieve($env);
		$this->assertSame($env, $res);
	}

	public function testAchieveWithDenial() {
		$this->expectException(Setup\UnachievableException::class);

		$env = $this->createMock(Setup\Environment::class);
		$confirmation_requester = $this->createMock(Setup\ConfirmationRequester::class);
		$achievement_tracker = $this->createMock(Setup\AchievementTracker::class);

		$env
			->method("getResource")
			->will($this->returnValueMap([
				[Setup\Environment::RESOURCE_CONFIRMATION_REQUESTER, $confirmation_requester],
				[Setup\Environment::RESOURCE_ACHIEVEMENT_TRACKER, $achievement_tracker]
			]));

		$confirmation_requester
			->expects($this->once())
			->method("confirmOrDeny")
			->with($this->message)
			->willReturn(false);

		$achievement_tracker
			->expects($this->once())
			->method("isAchieved")
			->with($this->o)
			->willReturn(false);

		$achievement_tracker
			->expects($this->never())
			->method("trackAchievementOf");

		$res = $this->o->achieve($env);
	}
}
