<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;

class GoalCollectionTest extends \PHPUnit\Framework\TestCase {
	protected function newGoal() {
		static $no = 0;

		$goal = $this
			->getMockBuilder(Setup\Goal::class)
			->setMethods(["getHash", "getLabel", "isNotable", "withResourcesFrom", "getPreconditions", "achieve"])
			->setMockClassName("Mock_GoalNo".($no++))
			->getMock();

		$goal
			->method("getHash")
			->willReturn("".$no);

		return $goal;
	}

	public function test_getHash() {
		$g1 = $this->newGoal();
		$g2 = $this->newGoal();
		$g3 = $this->newGoal();

		$c1 = new Setup\GoalCollection("", false, $g1, $g2, $g3);
		$c2 = new Setup\GoalCollection("", false, $g1, $g2);
		$c3 = new Setup\GoalCollection("", false, $g1, $g2, $g3);

		$this->assertIsString($c1->getHash());
		$this->assertIsString($c2->getHash());
		$this->assertIsString($c3->getHash());

		$this->assertEquals($c1->getHash(), $c1->getHash());
		$this->assertNotEquals($c1->getHash(), $c2->getHash());
		$this->assertEquals($c1->getHash(), $c3->getHash());
	}

	public function test_getLabel() {
		$c = new Setup\GoalCollection("LABEL", false);
		$this->assertEquals("LABEL", $c->getLabel());
	}

	public function test_isNotable() {
		$c1 = new Setup\GoalCollection("", false);
		$c2 = new Setup\GoalCollection("", true);
		$this->assertFalse($c1->isNotable());
		$this->assertTrue($c2->isNotable());
	}

	public function test_withResourcesFrom() {
		$g1 = $this->newGoal();
		$g2 = $this->newGoal();
		$g3 = $this->newGoal();

		$c = new Setup\GoalCollection("", false, $g1, $g2, $g3);

		$env = $this->createMock(Setup\Environment::class);

		foreach([$g1,$g2,$g3] as $g) {
			$g
				->expects($this->once())
				->method("withResourcesFrom")
				->with($env)
				->willReturn($g);
		}

		$c->withResourcesFrom($env);
	}

	public function test_getPreconditions() {
		$g1 = $this->newGoal();
		$g2 = $this->newGoal();
		$g3 = $this->newGoal();
		$g4 = $this->newGoal();
		$g5 = $this->newGoal();

		$c = new Setup\GoalCollection("", false, $g1, $g2, $g3);

		$env = $this->createMock(Setup\Environment::class);

		$g1
			->expects($this->once())
			->method("getPreconditions")
			->willReturn([]);
		$g2
			->expects($this->once())
			->method("getPreconditions")
			->willReturn([$g4, $g5]);
		$g3
			->expects($this->once())
			->method("getPreconditions")
			->willReturn([$g4]);

		$pre = $c->getPreconditions();
		$this->assertEquals([$g4,$g5], $pre);	
	}


	public function test_achieve() {
		$g1 = $this->newGoal();
		$g2 = $this->newGoal();
		$g3 = $this->newGoal();

		$c = new Setup\GoalCollection("", false, $g1, $g2, $g3);

		$env = $this->createMock(Setup\Environment::class);

		foreach([$g1,$g2,$g3] as $g) {
			$g
				->expects($this->once())
				->method("achieve")
				->with($env);
		}

		$c->achieve($env);
	}
}
