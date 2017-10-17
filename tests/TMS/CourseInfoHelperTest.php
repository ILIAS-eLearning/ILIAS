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

class TMS_CourseInfoHelperTest extends PHPUnit_Framework_TestCase {
	public function test_getCourseInfo() {
		$helper = $this
			->getMockBuilder(CourseInfoHelperTest::class)
			->setMethods(["getComponentsOfType"])
			->getMock();

		$component1 = $this->createMock(TMS\CourseInfo::class);
		$component2 = $this->createMock(TMS\CourseInfo::class);
		$component3 = $this->createMock(TMS\CourseInfo::class);

		$context = 23;
	
		$helper
			->expects($this->once())
			->method("getComponentsOfType")
			->willReturn([$component1, $component2, $component3]);

		$component1
			->expects($this->once())
			->method("hasContext")
			->with($context)
			->willReturn(false);

		$component2
			->expects($this->once())
			->method("hasContext")
			->with($context)
			->willReturn(true);
		$component2
			->expects($this->once())
			->method("getPriority")
			->willReturn(2);

		$component3
			->expects($this->once())
			->method("hasContext")
			->with($context)
			->willReturn(true);
		$component3
			->expects($this->once())
			->method("getPriority")
			->willReturn(1);

		$course_info = $helper->getCourseInfo($context);
		$this->assertEquals([$component3, $component2], $course_info);
	}
}
