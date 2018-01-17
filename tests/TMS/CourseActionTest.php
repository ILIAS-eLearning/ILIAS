<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

require_once(__DIR__."/../../Services/Object/classes/class.ilObject.php");
require_once(__DIR__."/../../Services/User/classes/class.ilObjUser.php");

use ILIAS\TMS;

class _CourseActionImpl extends TMS\CourseActionImpl {
	public function getLink(\ilCtrl $ctrl, $usr_id) {
		throw new Exception("mock me");
	}
	public function isAllowedFor($usr_id) {
		throw new Exception("mock me");
	}
	public function getLabel() {
		throw new Exception("mock me");
	}
}

class CourseActionTest extends PHPUnit_Framework_TestCase {
	public function test_fields() {
		$priority = 10;
		$contexts = [TMS\CourseAction::CONTEXT_SEARCH];
		$entity = $this->createMock(CaT\Ente\Entity::class);
		$owner = $this->createMock(ilObject::class);
		$user = $this->createMock(\ilObjUser::class);

		$user
			->expects($this->once())
			->method("getId");

		$action = new _CourseActionImpl($entity, $owner, $user, $priority, $contexts);

		$this->assertEquals($entity, $action->entity());
		$this->assertEquals($owner, $action->getOwner());
		$this->assertEquals($priority, $action->getPriority());
		$this->assertTrue($action->hasContext(TMS\CourseAction::CONTEXT_SEARCH));
		$this->assertFalse($action->hasContext(TMS\CourseAction::CONTEXT_USER_BOOKING));
	}
}
