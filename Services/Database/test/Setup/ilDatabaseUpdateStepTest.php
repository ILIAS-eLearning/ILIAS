<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

class ilDatabaseUpdateStepTest extends TestCase {
	protected function setUp(): void {
		$this->parent = $this->createMock(\ilDatabaseUpdateSteps::class);
		$this->precondition = $this->createMock(Objective::class);

		$this->step = new \ilDatabaseUpdateStep(
			$this->parent,
			"some_method",
			$this->precondition,
			$this->precondition	
		);
	}

	public function testGetPreconditions() {
		$env = $this->createMock(Environment::class);

		$this->assertEquals(
			[$this->precondition, $this->precondition],
			$this->step->getPreconditions($env)
		);
	}
}
