<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;

class GoalCollectionTest extends \PHPUnit\Framework\TestCase {
	public function testGetGoals() {
		$g1 = $this->newGoal();
		$g2 = $this->newGoal();
		$g3 = $this->newGoal();

		$c = new Setup\GoalCollection("", false, $g1, $g2, $g3);

		$this->assertEquals([$g1, $g2, $g3], $c->getGoals());
	}

	public function testGetHash() {
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

	public function testGetLabel() {
		$c = new Setup\GoalCollection("LABEL", false);
		$this->assertEquals("LABEL", $c->getLabel());
	}

	public function testIsNotable() {
		$c1 = new Setup\GoalCollection("", false);
		$c2 = new Setup\GoalCollection("", true);
		$this->assertFalse($c1->isNotable());
		$this->assertTrue($c2->isNotable());
	}

	public function testGetPreconditions() {
		$g1 = $this->newGoal();
		$g2 = $this->newGoal();
		$g3 = $this->newGoal();

		$c = new Setup\GoalCollection("", false, $g1, $g2, $g3);

		$env = $this->createMock(Setup\Environment::class);

		$pre = $c->getPreconditions($env);
		$this->assertEquals([$g1,$g2, $g3], $pre);	
	}


	public function testAchieve() {
		$g1 = $this->newGoal();
		$g2 = $this->newGoal();
		$g3 = $this->newGoal();

		$c = new Setup\GoalCollection("", false, $g1, $g2, $g3);

		$env = $this->createMock(Setup\Environment::class);

		foreach([$g1,$g2,$g3] as $g) {
			$g
				->expects($this->never())
				->method("achieve");
		}

		$res = $c->achieve($env);
		$this->assertSame($env, $res);
	}

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
}
