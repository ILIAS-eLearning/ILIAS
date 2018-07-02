<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS;

class TMS_CourseInfoTest extends PHPUnit_Framework_TestCase {
	public function test_fields() {
		$label = "LABEL";
		$value = "VALUE";
		$description = "DESCRIPTION";
		$priority = 10;
		$contexts = [TMS\CourseInfo::CONTEXT_SEARCH_SHORT_INFO];
		$entity = $this->createMock(CaT\Ente\Entity::class);
		$info = new TMS\CourseInfoImpl($entity, $label, $value, $description, $priority, $contexts);
		$this->assertEquals($entity, $info->entity());
		$this->assertEquals($label, $info->getLabel());
		$this->assertEquals($value, $info->getValue());
		$this->assertEquals($description, $info->getDescription());
		$this->assertEquals($priority, $info->getPriority());
		$this->assertTrue($info->hasContext(TMS\CourseInfo::CONTEXT_SEARCH_SHORT_INFO));
		$this->assertFalse($info->hasContext(TMS\CourseInfo::CONTEXT_SEARCH_DETAIL_INFO));
	}
}
