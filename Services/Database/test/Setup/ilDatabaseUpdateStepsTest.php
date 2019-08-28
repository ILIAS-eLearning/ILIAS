<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

use ILIAS\Setup\Environment;
use ILIAS\Setup\ObjectiveIterator;

class Test_ilDatabaseUpdateSteps extends ilDatabaseUpdateSteps {
	public $called = [];

	public function step_1(\ilDBInterface $db) {
		$this->called[] = 1;
		// Call some function on the interface to check if this step
		// is really called.
		$db->connect();
	}	

	// 4 comes before 2 to check if the class gets the sorting right
	public function step_4(\ilDBInterface $db) {
		$this->called[] = 4;
		// Call some function on the interface to check if this step
		// is really called.
		$db->connect();
	}

	public function step_2(\ilDBInterface $db) {
		$this->called[] = 2;
		// Call some function on the interface to check if this step
		// is really called.
		$db->connect();
	}

	public function _getSteps() {
		return self::getSteps();
	}
}

class ilDatabaseUpdateStepsTest extends TestCase {
	protected function setUp(): void {
		$this->config = $this->createMock(\ilDatabaseSetupConfig::class);

		$this->test1 = new Test_ilDatabaseUpdateSteps($this->config);
	}

	public function testGetAllSteps() {
		$steps = $this->test1->_getSteps();

		$expected = [
			1 => [$this->test1, "step_1"],
			2 => [$this->test1, "step_2"],
			4 => [$this->test1, "step_4"],
		];

		$this->assertEquals($expected, $steps);
	}

	public function testAchieveAllSteps() {
		$env = $this->createMock(Environment::class);
		$db = $this->createMock(\ilDBInterface::class);

		$env
			->method("getResource")
			->with(Environment::RESOURCE_DATABASE)
			->willReturn($db); 

		$db
			->expects($this->exactly(3))
			->method("connect");

		$i = new ObjectiveIterator($env, $this->test1);
		while($i->valid()) {
			$current = $i->current();
			$env = $current->achieve($env);
			$i->setEnvironment($env);
			$i->next();
		}

		$this->assertEquals([1,2,4], $this->test1->called);
	}
}
