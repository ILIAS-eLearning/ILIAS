+<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\CourseCreation\CourseListGUIExtension;
use PHPUnit\Framework\TestCase;

require_once(__DIR__."/../../../Services/Language/classes/class.ilLanguage.php");
require_once(__DIR__."/../../../Services/UICore/classes/class.ilTemplate.php");

class  _TMS_CourseCreation_CourseListGUIExtension_Parent {
	public $commands = [];
	public function getCommands() {
		return $this->commands;
	}
}

class _TMS_CourseCreation_CourseListGUIExtension extends _TMS_CourseCreation_CourseListGUIExtension_Parent {
	use CourseListGUIExtension;

	public $create_course_cmd = "CREATE_COURSE";
	protected function getCreateCourseCommand() {
		return $this->create_course_cmd;
	}

	public $create_course_cmd_link = "CREATE_COURSE_LINK";
	protected function getCreateCourseCommandLink() {
		return $this->create_course_cmd_link;
	}

	public $create_course_lng_var = "CREATE_COURSE_LNG_VAR";
	protected function getCreateCourseCommandLngVar() {
		return $this->create_course_lng_var;
	}

	public $create_course_access_granted = true;
	protected function getCreateCourseAccessGranted() {
		return $this->create_course_access_granted;
	}

	public $no_open_requests = true;
	protected function noOpenRequests() {
		return $this->no_open_requests;
	}
}

class _TMS_CourseCreation_CourseListGUIExtension_Bare {
	use CourseListGUIExtension;

	public function _getCreateCourseCommand() {
		return $this->getCreateCourseCommand();
	}

	public function _getCreateCourseCommandLink() {
		return $this->getCreateCourseCommandLink();
	}

	public function _getCreateCourseCommandLngVar() {
		return $this->getCreateCourseCommandLngVar();
	}

	public function _getCreateCourseAccessGranted() {
		return $this->getCreateCourseAccessGranted();
	}
}

class TMS_CourseCreation_CourseListGUIExtensionTest extends TestCase {
	const CREATE_COURSE_ACTION_LNG_VAR = "create_course_from_template";

	public function setUp() {
		$this->gui_fake = new _TMS_CourseCreation_CourseListGUIExtension();
		$this->bare = new _TMS_CourseCreation_CourseListGUIExtension_Bare();
	}

	public function test_enhances_getCommands() {
		$base = [1,2,3,4];
		$this->gui_fake->parent_ref_id = 10;
		$this->gui_fake->ref_id = 20;
		$this->gui_fake->commands = $base;
		$commands = $this->gui_fake->getCommands();
		$this->assertCount(count($base)+1, $commands);
	}

	public function test_inserts_create_command_in_getCommands() {
		$this->gui_fake->parent_ref_id = 10;
		$this->gui_fake->ref_id = 20;
		$commands = $this->gui_fake->getCommands();
		$expected = 
			[["cmd" => $this->gui_fake->create_course_cmd
			, "link" => $this->gui_fake->create_course_cmd_link
			, "frame" => ""
			, "lang_var" => $this->gui_fake->create_course_lng_var
			, "txt" => null
			, "granted" => $this->gui_fake->create_course_access_granted
			, "access_info" => null
			, "img" => null
			, "default" => null
			]];
		$this->assertEquals($expected, $commands);
	}

	public function test_does_not_insert_command_if_no_access() {
		$this->gui_fake->create_course_access_granted = false;
		$commands = $this->gui_fake->getCommands();
		$this->assertCount(0, $commands);
	}

	public function test_does_not_insert_command_if_access_and_open_requests() {
		$this->gui_fake->create_course_access_granted = true;
		$this->gui_fake->no_open_requests = false;
		$commands = $this->gui_fake->getCommands();
		$this->assertCount(0, $commands);
	}

	public function test_getCreateCourseCommandLngVar() {
		$this->bare->lng = $this->createMock(\ilLanguage::class);

		$this->bare->lng
			->expects($this->once())
			->method("loadLanguageModule")
			->with("tms");

		$id = $this->bare->_getCreateCourseCommandLngVar();

		$this->assertEquals(self::CREATE_COURSE_ACTION_LNG_VAR, $id);
	}
}
