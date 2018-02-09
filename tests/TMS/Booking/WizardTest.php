<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Booking;

require_once(__DIR__."/../../../Services/Form/classes/class.ilPropertyFormGUI.php");

class _BookingWizard extends Booking\Wizard{
	public function _getSortedSteps() {
		return $this->getSortedSteps();
	}
	public function _getApplicableSteps() {
		return $this->getApplicableSteps();
	}
	public function _getUserId() {
		return $this->getUserId();
	}
	public function _getEntityRefId() {
		return $this->getEntityRefId();
	}
	public function _getDIC() {
		return $this->getDIC();
	}
	public function _getComponentClass() {
		return $this->getComponentClass();
	}
}

class TMS_Booking_WizardTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->step_count = 0;
	}

	public function createStepMock() {
		$this->step_count++;
		return $this->getMockBuilder(Booking\Step::class)
			->disableOriginalConstructor()
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMockClassName("BookingStep".$this->step_count)
			->getMock();
	}

	public function test_getId() {
		$component_class = "SOME_CLASS";
		$wizard_id = "{$component_class}_1_2_3";
		$wizard = new _BookingWizard([], $component_class, 1, 2, 3, null);
		$this->assertSame($wizard_id, $wizard->getId());
	}

	public function test_getDIC() {
		$dic = ["my" => "container"];
		$wizard = new _BookingWizard($dic, "", 0, 0, 0, null);
		$this->assertSame($dic, $wizard->_getDIC());
	}

	public function test_getUserId() {
		$user_id = 42;
		$wizard = new _BookingWizard([], "", 0, 0, $user_id, null);
		$this->assertEquals($user_id, $wizard->_getUserId());
	}

	public function test_getEntityRefId() {
		$crs_id = 23;
		$wizard = new _BookingWizard([], "", 0, $crs_id, 0, null);
		$this->assertEquals($crs_id, $wizard->_getEntityRefId());
	}

	public function test_getComponentClass() {
		$component_class = "THIS_IS_COMPONENT_CLASS";
		$wizard = new _BookingWizard([], $component_class, 0, 0, 0, null);
		$this->assertEquals($component_class, $wizard->_getComponentClass());
	}

	public function test_getSortedSteps() {
		$wizard = $this->getMockBuilder(_BookingWizard::class)
			->setMethods(["getApplicableSteps"])
			->disableOriginalConstructor()
			->getMock();

		$component1 = $this->createStepMock();
		$component2 = $this->createStepMock();
		$component3 = $this->createStepMock();

		$component1
			->expects($this->atLeast(1))
			->method("getPriority")
			->willReturn(2);
		$component2
			->expects($this->atLeast(1))
			->method("getPriority")
			->willReturn(3);
		$component3
			->expects($this->atLeast(1))
			->method("getPriority")
			->willReturn(1);

		$wizard
			->expects($this->once())
			->method("getApplicableSteps")
			->willReturn([$component1, $component2, $component3]);

		$steps = $wizard->_getSortedSteps();

		$this->assertEquals([$component3, $component1, $component2], $steps);
	}

	public function test_getApplicableSteps() {
		$wizard = $this->getMockBuilder(_BookingWizard::class)
			->setMethods(["getComponentsOfType", "getUserId", "getComponentClass"])
			->disableOriginalConstructor()
			->getMock();

		$user_id = 23;
		$wizard
			->expects($this->atLeast(1))
			->method("getUserId")
			->willReturn($user_id);

		$component1 = $this->createStepMock();
		$component2 = $this->createStepMock();
		$component3 = $this->createStepMock();

		$component1
			->expects($this->atLeast(1))
			->method("isApplicableFor")
			->with($user_id)
			->willReturn(true);
		$component2
			->expects($this->atLeast(1))
			->method("isApplicableFor")
			->with($user_id)
			->willReturn(false);
		$component3
			->expects($this->atLeast(1))
			->method("isApplicableFor")
			->with($user_id)
			->willReturn(true);

		$wizard
			->expects($this->once())
			->method("getComponentClass")
			->willReturn(Booking\Step::class);

		$wizard
			->expects($this->once())
			->method("getComponentsOfType")
			->with($this->equalTo(Booking\Step::class))
			->willReturn([$component1, $component2, $component3]);

		$steps = $wizard->_getApplicableSteps();

		$this->assertEquals([$component1, $component3], $steps);
	}


	public function test_getSteps() {
		$active_user_id = 1;
		$crs_ref_id = 2;
		$target_user_id = 3;

		$wizard = $this->getMockBuilder(_BookingWizard::class)
			->setMethods(["getSortedSteps"])
			->setConstructorArgs([[], "", $active_user_id, $crs_ref_id, $target_user_id, null])
			->getMock();

		$component1 = $this->createStepMock();
		$component2 = $this->createStepMock();
		$component3 = $this->createStepMock();

		$wizard
			->expects($this->once())
			->method("getSortedSteps")
			->willReturn([$component1, $component2, $component3]);

		$steps = $wizard->getSteps();

		$this->assertCount(3, $steps);
		list($step1, $step2, $step3) = $steps;

		$this->assertInstanceOf(\ILIAS\TMS\Wizard\Step::class, $step1);
		$this->assertInstanceOf(\ILIAS\TMS\Wizard\Step::class, $step2);
		$this->assertInstanceOf(\ILIAS\TMS\Wizard\Step::class, $step3);

		$component1
			->expects($this->once())
			->method("processStep")
			->with($crs_ref_id, $target_user_id, []);

		$step1->processStep([]);
	}

}
