<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\CourseCreation;

require_once(__DIR__."/../../../Services/Form/classes/class.ilPropertyFormGUI.php");

class TMS_CourseCreation_RequestTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->id = 23;
		$this->user_id = 43;
		$this->session_id = "SESSION_ID";
		$this->crs_ref_id = 1337;
		$this->new_parent_ref_id = 2342;
		$this->request_ts = new \DateTime("1985-04-05 13:37");
		$this->target_ref_id = 4242;
		$this->finished_ts = new \DateTime("now");
		$this->request = new CourseCreation\Request($this->id, $this->user_id, $this->session_id, $this->crs_ref_id, $this->new_parent_ref_id, [], [], $this->request_ts, $this->target_ref_id, $this->finished_ts);
	}

	public function test_getId() {
		$this->assertEquals($this->id, $this->request->getId());
	}

	public function test_getUserId() {
		$this->assertEquals($this->user_id, $this->request->getUserId());
	}

	public function test_getSessionId() {
		$this->assertEquals($this->session_id, $this->request->getSessionId());
	}

	public function test_getCourseRefId() {
		$this->assertEquals($this->crs_ref_id, $this->request->getCourseRefId());
	}

	public function test_getRequestedTS() {
		$this->assertEquals($this->request_ts, $this->request->getRequestedTS());
	}

	public function test_getTargetRefId() {
		$this->assertEquals($this->target_ref_id, $this->request->getTargetRefId());
	}

	public function test_getNewParentRefId() {
		$this->assertEquals($this->new_parent_ref_id, $this->request->getNewParentRefId());
	}

	public function test_targetRefId_is_nullable() {
		$request = new CourseCreation\Request($this->id, $this->user_id, $this->session_id, $this->crs_ref_id, $this->new_parent_ref_id, [], [], $this->request_ts, null, null);
		$this->assertEquals(null, $request->getTargetRefId());
	}

	public function test_getFinishedTS() {
		$this->assertEquals($this->finished_ts, $this->request->getFinishedTS());
	}

	public function test_withTargetRefIdAndFinishedTS() {
		$ref = 2323;
		$new_ts = new \DateTime("2000-12-31 23:59");
		$clone = $this->request->withTargetRefIdAndFinishedTS($ref, $new_ts);

		$this->assertEquals($this->finished_ts, $this->request->getFinishedTS());
		$this->assertEquals($ref, $clone->getTargetRefId());
		$this->assertEquals($new_ts, $clone->getFinishedTS());
	}

	public function test_withFinishedTS() {
		$new_ts = new \DateTime("2000-12-31 23:59");
		$clone = $this->request->withFinishedTS($new_ts);

		$this->assertEquals($this->finished_ts, $this->request->getFinishedTS());
		$this->assertEquals($this->target_ref_id, $clone->getTargetRefId());
		$this->assertEquals($new_ts, $clone->getFinishedTS());
	}

	public function test_finishedTS_is_nullable() {
		$request = new CourseCreation\Request($this->id, $this->user_id, $this->session_id, $this->crs_ref_id, $this->new_parent_ref_id, [], [], $this->request_ts, null);
		$this->assertEquals(null, $request->getFinishedTS());
	}

	public function test_getCopyOptionFor() {
		$options =
			[ 123 => 2
			, 456 => 3
			];

		$this->request = new CourseCreation\Request($this->id, $this->user_id, $this->session_id, $this->crs_ref_id, $this->new_parent_ref_id, $options, [], $this->request_ts, $this->target_ref_id, $this->finished_ts);

		$this->assertEquals(2, $this->request->getCopyOptionFor(123));
		$this->assertEquals(3, $this->request->getCopyOptionFor(456));
		$this->assertEquals(1, $this->request->getCopyOptionFor(789));
	}

	public function test_getConfigurationFor() {
		$obj1 = new \stdClass();
		$obj2 = new \stdClass();
		$configuration =
			[ 123 => [$obj1]
			, 456 => [$obj2]
			];

		$this->request = new CourseCreation\Request($this->id, $this->user_id, $this->session_id, $this->crs_ref_id, $this->new_parent_ref_id, [], $configuration, $this->request_ts, $this->target_ref_id, $this->finished_ts);

		$this->assertSame([$obj1], $this->request->getConfigurationFor(123));
		$this->assertSame([$obj2], $this->request->getConfigurationFor(456));
		$this->assertNull($this->request->getConfigurationFor(789));
	}
}
