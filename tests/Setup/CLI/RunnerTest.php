<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;

class RunnerTest extends \PHPUnit\Framework\TestCase {
	public function testBasicAlgorithm() {
		$goal = $this->newGoal();
		$config = $this->createMock(Setup\Config::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$runner = new Setup\CLI\Runner($environment, $goal);

		$goal
			->expects($this->once())
			->method("getPreconditions")
			->willReturn([]);

		$goal
			->expects($this->once())
			->method("achieve")
			->with($environment)
			->willReturn($environment);

		$runner->run();
	}

	public function testAllGoals() {
		$goal1 = $this->newGoal();
		$goal11 = $this->newGoal();
		$goal12 = $this->newGoal();
		$goal121 = $this->newGoal();

		$goal1
			->method("getPreconditions")
			->willReturn([$goal11, $goal12]);

		$goal11
			->method("getPreconditions")
			->willReturn([]);

		$goal12
			->method("getPreconditions")
			->willReturn([$goal121]);

		$goal121
			->method("getPreconditions")
			->willReturn([]);

		$config = $this->createMock(Setup\Config::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$runner = new Setup\CLI\Runner($environment, $goal1);

		$f = function($g) { return $g->getHash(); };
		$expected = array_map($f, [$goal11, $goal121, $goal12, $goal1]);
		$result = array_map($f, iterator_to_array($runner->allGoals()));

		$this->assertEquals($expected, $result);
	}

	public function testAllGoalsOnlyReturnsGoalOnce() {
		$goal1 = $this->newGoal();
		$goal11 = $this->newGoal();

		$goal1
			->method("getPreconditions")
			->willReturn([$goal11, $goal11]);

		$goal11
			->method("getPreconditions")
			->willReturn([]);

		$config = $this->createMock(Setup\Config::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$runner = new Setup\CLI\Runner($environment, $goal1);

		$f = function($g) { return $g->getHash(); };
		$expected = array_map($f, [$goal11, $goal1]);
		$result = array_map($f, iterator_to_array($runner->allGoals()));

		$this->assertEquals($expected, $result);
	}

	public function testAllGoalsDetectsCycle() {
		$goal1 = $this->newGoal();
		$goal2 = $this->newGoal();

		$goal1
			->method("getPreconditions")
			->willReturn([$goal2]);

		$goal2
			->method("getPreconditions")
			->willReturn([$goal1]);

		$config = $this->createMock(Setup\Config::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$runner = new Setup\CLI\Runner($environment, $goal1);

		$this->expectException(Setup\UnachievableException::class);		
		iterator_to_array($runner->allGoals());
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
