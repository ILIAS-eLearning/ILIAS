<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Wizard;

class TMS_Wizard_StateTest extends PHPUnit_Framework_TestCase {
	public function test_getStepData_throws() {
		$state = new Wizard\State("id",0);
		$catched = false;
		try {
			$state->getStepData(0);
			$this->assertFalse("This should not happen.");
		}
		catch (\OutOfBoundsException $e) {
			$catched = true;
		}
		$this->assertTRue($catched);
	}

	public function test_getStepData_by_constructor() {
		$data = "data";
		$encoded = json_encode($data);
		$state = new Wizard\State("id",0, [0 => $encoded]);
		$data2 = $state->getStepData(0);
		$this->assertEquals($data, $data2);
	}

	public function test_withStepData() {
		$data = "data";
		$state = new Wizard\State("id",0, []);
		$state2 = $state->withStepData(0, $data);
		$this->assertNotSame($state, $state2);
		$this->assertEquals($data, $state2->getStepData(0));
	}

	public function test_getAllStepData() {
		$data = "data";
		$encoded = json_encode($data);
		$state = (new Wizard\State("id",0, []))
					->withStepData(0, $data);
		$this->assertEquals([0 => $encoded], $state->getAllStepData());
	}

	public function test_withNextStep() {
		$state = (new Wizard\State("id",0, []))
			->withNextStep();
		$this->assertEquals(1, $state->getStepNumber());
	}

	public function test_withPrevousStep() {
		$state = (new Wizard\State("id",0, []))
			->withNextStep()
			->withNextStep()
			->withPreviousStep();
		$this->assertEquals(1, $state->getStepNumber());
	}

	public function test_hasStepData() {
		$data = "data";
		$state = new Wizard\State("id",0, []);
		$state2 = $state->withStepData(0, $data);
		$this->assertNotSame($state, $state2);

		$this->assertTrue($state2->hasStepData(0));
	}

	public function test_hasNoStepData() {
		$state = new Wizard\State("id",0, []);

		$this->assertFalse($state->hasStepData(0));
	}

	public function test_getWizardId() {
		$state = new Wizard\State("id",0, []);

		$this->assertEquals("id", $state->getWizardId());
	}
}
