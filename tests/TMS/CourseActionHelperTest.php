<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS;

require_once(__DIR__."/../../Services/UICore/classes/class.ilCtrl.php");
require_once(__DIR__."/../../Services/User/classes/class.ilObjUser.php");

class CourseActionHelperTest {
	use TMS\CourseActionHelper;

	public function getComponentsOfType($component_type) {
		throw new \LogicException("mock me");
	}
}

class CourseActionMock implements TMS\CourseAction {
	public function __construct($has_context, $priority) {
		$this->has_context = $has_context;
		$this->priority = $priority;
	}
	public function hasContext($context) {
		return $this->has_context;
	}
	public function getPriority() {
		return $this->priority;
	}
	public function getLabel() {
		throw new \LogicException("NYI!");
	}
	public function getLink(\ilCtrl $ctrl, $usr_id) {
		throw new \LogicException("NYI!");
	}
	public function isAllowedFor($usr_id) {
		throw new \LogicException("NYI!");
	}
	public function getOwner() {
		throw new \LogicException("NYI!");
	}
	public function entity() {
		throw new \LogicException("NYI!");
	}
}

class TMS_CourseActionHelperTest extends PHPUnit_Framework_TestCase {
	public function test_getCourseInfo() {
		$helper = $this
			->getMockBuilder(CourseActionHelperTest::class)
			->setMethods(["getComponentsOfType"])
			->getMock();

		$component1 = new CourseActionMock(false, 0);
		$component2 = new CourseActionMock(true, 2);
		$component3 = new CourseActionMock(true, 1);

		$context = 1;

		$helper
			->expects($this->once())
			->method("getComponentsOfType")
			->willReturn([$component1, $component2, $component3]);

		$course_action = $helper->getCourseAction($context);
		$this->assertEquals([$component3, $component2], $course_action);
	}
}
