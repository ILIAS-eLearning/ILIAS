<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Wizard;

class TMS_Wizard_SessionStateDBTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->db = $this->getMockBuilder(Wizard\SessionStateDB::class)
			->setMethods(["sessionGet", "sessionSet", "sessionClear"])
			->disableOriginalConstructor()
			->getMock();
		$this->state = $this->createMock(Wizard\State::class);
	}

	public function test_load() {
		$wizard_id = "WIZARD_ID";

		$step_number = 43;
		$data = ["some_data"]; 
		$this->db
			->expects($this->once())
			->method("sessionGet")
			->with($wizard_id)
			->willReturn(json_encode(["step_number" => $step_number, "step_data" => $data]));

		$expected = new Wizard\State($wizard_id, $step_number, $data);
		$this->assertEquals($expected, $this->db->load($wizard_id));
	}

	public function test_save() {
		$wizard_id = "WIZARD_ID";
		$this->state
			->expects($this->once())
			->method("getWizardId")
			->willReturn($wizard_id);

		$step_number = 23;
		$this->state
			->expects($this->once())
			->method("getStepNumber")
			->willReturn($step_number);

		$data = ["some_other_data"];
		$this->state
			->expects($this->once())
			->method("getAllStepData")
			->willReturn($data);

		$this->db
			->expects($this->once())
			->method("sessionSet")
			->with($wizard_id, json_encode(["step_number" => $step_number, "step_data" => $data]));

		$this->db->save($this->state);
	}

	public function test_delete() {
		$wizard_id = "WIZARD_ID";
		$this->state
			->expects($this->once())
			->method("getWizardId")
			->willReturn($wizard_id);

		$this->db
			->expects($this->once())
			->method("sessionClear")
			->with($wizard_id);

		$this->db->delete($this->state);
	}
}
