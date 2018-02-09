<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Booking;

class TMS_Booking_StepAdapterTest extends PHPUnit_Framework_TestCase {
	public function setUp () {
		$this->user_id = 667; // the neighbour of the beast
		$this->crs_ref_id = 11;
		$this->wrapped = $this->createMock(Booking\Step::class);
		$this->adapter = new Booking\StepAdapter($this->wrapped, $this->crs_ref_id, $this->user_id);
	}

	public function test_getLabel() {
		$label = "LABEL";
		$this->wrapped
			->expects($this->once())
			->method("getLabel")
			->willReturn($label);

		$label2 = $this->adapter->getLabel();

		$this->assertEquals($label, $label2);
	}

	public function test_getDescription() {
		$description = "DESCRIPTION";
		$this->wrapped
			->expects($this->once())
			->method("getDescription")
			->willReturn($description);

		$description2 = $this->adapter->getDescription();

		$this->assertEquals($description, $description2);
	}

	public function test_appendToStepForm() {
		$form = $this->createMock(\ilPropertyFormGUI::class);

		$this->wrapped
			->expects($this->once())
			->method("appendToStepForm")
			->with($form, $this->user_id);

		$this->adapter->appendToStepForm($form);
	}

	public function test_getData() {
		$form = $this->createMock(\ilPropertyFormGUI::class);

		$data = "DATA";
		$this->wrapped
			->expects($this->once())
			->method("getData")
			->with($form)
			->willReturn($data);

		$data2 = $this->adapter->getData($form);

		$this->assertEquals($data, $data2);
	}

	public function test_addDataToForm() {
		$form = $this->createMock(\ilPropertyFormGUI::class);
		$data = "SOME MORE DATA";

		$this->wrapped
			->expects($this->once())
			->method("addDataToForm")
			->with($form, $data);

		$this->adapter->addDataToForm($form, $data);
	}

	public function test_appendToOverviewForm() {
		$form = $this->createMock(\ilPropertyFormGUI::class);
		$data = "EVEN MORE DATA";

		$this->wrapped
			->expects($this->once())
			->method("appendToOverviewForm")
			->with($data, $form, $this->user_id);

		$this->adapter->appendToOverviewForm($form, $data);
	}

	public function	test_processStep() {
		$data = "EVEN MORE DATA";

		$this->wrapped
			->expects($this->once())
			->method("processStep")
			->with($this->crs_ref_id, $this->user_id, $data);

		$this->adapter->processStep($data);
	}
}
