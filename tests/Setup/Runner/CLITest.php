<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;

class CLITest extends \PHPUnit\Framework\TestCase {
	protected function newGoal() {
		static $no = 0;

		$goal = $this
			->getMockBuilder(Setup\Goal::class)
			->setMethods(["getHash", "getType", "getLabel", "isNotable", "withConfiguration", "getDefaultConfiguration", "withResourcesFrom", "getConfigurationInput", "getPreconditions", "achieve"])
			->setMockClassName("Mock_GoalNo".($no++))
			->getMock();

		$goal
			->method("getHash")
			->willReturn("".$no);

		return $goal;
	}

	public function testBasicAlgorithm() {
		$goal = $this->newGoal();
		$config = $this->createMock(Setup\Config::class);
		$configuration_loader = $this->createMock(Setup\ConfigurationLoader::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$runner = new Setup\Runner\CLI($environment, $configuration_loader, $goal);

		$configuration_loader
			->expects($this->once())
			->method("loadConfigurationFor")
			->with($type)
			->willReturn($config);

		$goal
			->expects($this->once())
			->method("getType")
			->willReturn($type);

		$goal
			->expects($this->once())
			->method("withConfiguration")
			->with($config)
			->willReturn($goal);

		$goal
			->expects($this->once())
			->method("withResourcesFrom")
			->with($environment)
			->willReturn($goal);

		$goal
			->expects($this->once())
			->method("getPreconditions")
			->willReturn([]);

		$goal
			->expects($this->once())
			->method("achieve")
			->with($environment)
			->willReturn(null);	

		$runner->run();
	}

	public function testAllGoals() {
		$goal1 = $this->newGoal();
		$goal11 = $this->newGoal();
		$goal12 = $this->newGoal();
		$goal121 = $this->newGoal();

		$goal1
			->method("getPreconditions")
			->will(
				$this->onConsecutiveCalls([$goal11, $goal12], [])
			);

		$goal11
			->method("getPreconditions")
			->willReturn([]);

		$goal12
			->method("getPreconditions")
			->will(
				$this->onConsecutiveCalls([$goal121], [])
			);

		$goal121
			->method("getPreconditions")
			->willReturn([]);

		$config = $this->createMock(Setup\Config::class);
		$configuration_loader = $this->createMock(Setup\ConfigurationLoader::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$configuration_loader
			->method("loadConfigurationFor")
			->with($type)
			->willReturn($config);

		foreach([$goal1, $goal11, $goal12, $goal121] as $goal) {
			$goal
				->method("getType")
				->willReturn($type);
			$goal
				->expects($this->atLeastOnce())
				->method("withResourcesFrom")
				->with($environment)
				->willReturn($goal);
			$goal
				->expects($this->atLeastOnce())
				->method("withConfiguration")
				->with($config)
				->willReturn($goal);
		}

		$runner = new Setup\Runner\CLI($environment, $configuration_loader, $goal1);

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
			->will(
				$this->onConsecutiveCalls([$goal11, $goal11], [])
			);

		$goal11
			->method("getPreconditions")
			->willReturn([]);

		$config = $this->createMock(Setup\Config::class);
		$configuration_loader = $this->createMock(Setup\ConfigurationLoader::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$configuration_loader
			->method("loadConfigurationFor")
			->with($type)
			->willReturn($config);

		foreach([$goal1, $goal11] as $goal) {
			$goal
				->method("getType")
				->willReturn($type);
			$goal
				->expects($this->atLeastOnce())
				->method("withResourcesFrom")
				->with($environment)
				->willReturn($goal);
			$goal
				->expects($this->atLeastOnce())
				->method("withConfiguration")
				->with($config)
				->willReturn($goal);
		}

		$runner = new Setup\Runner\CLI($environment, $configuration_loader, $goal1);

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
		$configuration_loader = $this->createMock(Setup\ConfigurationLoader::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$configuration_loader
			->method("loadConfigurationFor")
			->with($type)
			->willReturn($config);

		foreach([$goal1, $goal2] as $goal) {
			$goal
				->method("getType")
				->willReturn($type);
			$goal
				->expects($this->atLeastOnce())
				->method("withResourcesFrom")
				->with($environment)
				->willReturn($goal);
			$goal
				->expects($this->atLeastOnce())
				->method("withConfiguration")
				->with($config)
				->willReturn($goal);
		}

		$runner = new Setup\Runner\CLI($environment, $configuration_loader, $goal1);

		$this->expectException(Setup\UnachievableException::class);		
		iterator_to_array($runner->allGoals());
	}

}
