<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS;

class CourseInfoHelperTest {
	use TMS\CourseInfoHelper;

	public function getComponentsOfType($component_type) {
		throw new \LogicException("mock me");
	}

	public function getUIFactory() {
		throw new \LogicException("mock me");
	}
}

class CourseInfoMock implements TMS\CourseInfo {
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
	public function getValue() {
		throw new \LogicException("NYI!");
	}
	public function getDescription() {
		throw new \LogicException("NYI!");
	}
	public function entity() {
		throw new \LogicException("NYI!");
	}
}

class TMS_CourseInfoHelperTest extends PHPUnit_Framework_TestCase {
	public function test_getCourseInfo() {
		$helper = $this
			->getMockBuilder(CourseInfoHelperTest::class)
			->setMethods(["getComponentsOfType"])
			->getMock();

		$component1 = new CourseInfoMock(false, 0);
		$component2 = new CourseInfoMock(true, 2);
		$component3 = new CourseInfoMock(true, 1);

		$context = 23;
	
		$helper
			->expects($this->once())
			->method("getComponentsOfType")
			->willReturn([$component1, $component2, $component3]);

		$course_info = $helper->getCourseInfo($context);
		$this->assertEquals([$component3, $component2], $course_info);
	}
}
