<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

use ILIAS\Setup\Environment;
use ILIAS\Setup\ObjectiveIterator;
use ILIAS\Setup\Objective;

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

	public function _getSteps() : array {
		return $this->getSteps();
	}

	public function _getStepsBefore(string $other) : array {
		return $this->getStepsBefore($other);
	}
}

class ilDatabaseUpdateStepsTest extends TestCase {
	protected function setUp(): void {
		$this->base = $this->createMock(Objective::class);

		$this->test1 = new Test_ilDatabaseUpdateSteps($this->base);
	}

	public function testGetStep1() {
		$env = $this->createMock(Environment::class);

		$step1 = $this->test1->getStep("step_1");

		$this->assertInstanceOf(ilDatabaseUpdateStep::class, $step1);
		$this->assertEquals(
			hash("sha256", Test_ilDatabaseUpdateSteps::class."::step_1"),
			$step1->getHash()
		);

		$preconditions = $step1->getPreconditions($env);

		$this->assertCount(1, $preconditions);
		$this->assertSame($this->base, $preconditions[0]);
	}

	public function testGetStep2() {
		$env = $this->createMock(Environment::class);

		$step1 = $this->test1->getStep("step_1");
		$step2 = $this->test1->getStep("step_2");

		$this->assertInstanceOf(ilDatabaseUpdateStep::class, $step2);
		$this->assertEquals(
			hash("sha256", Test_ilDatabaseUpdateSteps::class."::step_2"),
			$step2->getHash()
		);

		$preconditions = $step2->getPreconditions($env);

		$this->assertCount(1, $preconditions);
		$this->assertEquals($step1->getHash(), $preconditions[0]->getHash());
	}

	public function testGetAllSteps() {
		$steps = $this->test1->_getSteps();

		$expected = [
			"step_1",
			"step_2",
			"step_4",
		];

		$this->assertEquals($expected, array_values($steps));
	}

	public function testGetStepsBeforeStep1() {
		$steps = $this->test1->_getStepsBefore("step_1");

		$expected = [
		];

		$this->assertEquals($expected, array_values($steps));
	}

	public function testGetStepsBeforeStep2() {
		$steps = $this->test1->_getStepsBefore("step_2");

		$expected = [
			"step_1"
		];

		$this->assertEquals($expected, array_values($steps));
	}

	public function testGetStepsBeforeStep4() {
		$steps = $this->test1->_getStepsBefore("step_4");

		$expected = [
			"step_1",
			"step_2"
		];

		$this->assertEquals($expected, array_values($steps));
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

		$this->base
			->method("getPreconditions")
			->willReturn([]);

		$this->base
			->expects($this->once())
			->method("achieve")
			->with($env)
			->willReturn($env);

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
