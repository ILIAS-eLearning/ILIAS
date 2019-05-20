<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;

class GoalIteratorTest extends \PHPUnit\Framework\TestCase {
	public function testBasicAlgorithm() {
		$hash = "my hash";
		$goal = $this->newGoal($hash);
		$environment = $this->createMock(Setup\Environment::class);

		$goal
			->expects($this->once())
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$iterator = new Setup\GoalIterator($environment, $goal);

		$this->assertTrue($iterator->valid());
		$this->assertSame($goal, $iterator->current());
		$this->assertSame($hash, $iterator->key());

		$iterator->next();

		$this->assertFalse($iterator->valid());
	}

	public function testRewind() {
		$hash = "my hash";
		$goal = $this->newGoal($hash);
		$environment = $this->createMock(Setup\Environment::class);

		$iterator = new Setup\GoalIterator($environment, $goal);

		$goal
			->expects($this->once())
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$iterator->next();
		$iterator->rewind();

		$this->assertTrue($iterator->valid());
		$this->assertSame($goal, $iterator->current());
		$this->assertSame($hash, $iterator->key());
	}

	public function testAllGoals() {
		$environment = $this->createMock(Setup\Environment::class);

		$goal1 = $this->newGoal();
		$goal11 = $this->newGoal();
		$goal12 = $this->newGoal();
		$goal121 = $this->newGoal();

		$goal1
			->method("getPreconditions")
			->with($environment)
			->willReturn([$goal11, $goal12]);

		$goal11
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$goal12
			->method("getPreconditions")
			->with($environment)
			->willReturn([$goal121]);

		$goal121
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$iterator = new Setup\GoalIterator($environment, $goal1);

		$expected = [
			$goal11->getHash() => $goal11,
			$goal121->getHash() => $goal121,
			$goal12->getHash() => $goal12,
			$goal1->getHash() => $goal1
		];

		$this->assertEquals($expected, iterator_to_array($iterator));
	}

	public function testAllGoalsOnlyReturnsGoalOnce() {
		$environment = $this->createMock(Setup\Environment::class);

		$goal1 = $this->newGoal();
		$goal11 = $this->newGoal();

		$goal1
			->method("getPreconditions")
			->with($environment)
			->willReturn([$goal11, $goal11]);

		$goal11
			->method("getPreconditions")
			->with($environment)
			->willReturn([]);

		$iterator = new Setup\GoalIterator($environment, $goal1);

		$expected = [
			$goal11->getHash() => $goal11,
			$goal1->getHash() => $goal1
		];
		$this->assertEquals($expected, iterator_to_array($iterator));
	}

	public function testAllGoalsDetectsCycle() {
		$environment = $this->createMock(Setup\Environment::class);

		$goal1 = $this->newGoal();
		$goal2 = $this->newGoal();

		$goal1
			->method("getPreconditions")
			->with($environment)
			->willReturn([$goal2]);

		$goal2
			->method("getPreconditions")
			->with($environment)
			->willReturn([$goal1]);

		$this->expectException(Setup\UnachievableException::class);		

		$iterator = new Setup\GoalIterator($environment, $goal1);
		iterator_to_array($iterator);
	}

	public function testSetEnvironment() {
		$env1 = new Setup\ArrayEnvironment([]);
		$env2 = new Setup\ArrayEnvironment([]);

		$goal1 = $this->newGoal();
		$goal2 = $this->newGoal();

		$goal1
			->expects($this->atLeastOnce())
			->method("getPreconditions")
			->with($env1)
			->willReturn([$goal2]);

		$goal2
			->expects($this->atLeastOnce())
			->method("getPreconditions")
			->with($env2)
			->willReturn([]);

		$iterator = new Setup\GoalIterator($env1, $goal1);

		$iterator->setEnvironment($env2);
		$iterator->next();
	}

	protected function newGoal($hash = null) {
		static $no = 0;

		$goal = $this
			->getMockBuilder(Setup\Goal::class)
			->setMethods(["getHash", "getLabel", "isNotable", "withResourcesFrom", "getPreconditions", "achieve"])
			->setMockClassName("Mock_GoalNo".($no++))
			->getMock();

		$goal
			->method("getHash")
			->willReturn($hash ?? "".$no);

		return $goal;
	}
}
