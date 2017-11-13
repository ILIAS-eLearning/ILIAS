<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Booking;

require_once(__DIR__."/../../../Services/Form/classes/class.ilPropertyFormGUI.php");

class BookingPlayerForTest extends Booking\Player {
	public function _getSortedSteps() {
		return $this->getSortedSteps();
	}
	public function _getApplicableSteps() {
		return $this->getApplicableSteps();
	}
	public function _getUserId() {
		return $this->getUserId();
	}
	public function _getProcessState() {
		return $this->getProcessState();
	}
	public function _saveProcessState($state) {
		return $this->saveProcessState($state);
	}
	public function _deleteProcessState($state) {
		return $this->deleteProcessState($state);
	}
	protected function getForm() {
		throw new \LogicException("Mock me!");
	}
	protected function txt($id) {
		return $id;
	}
	protected function redirectToPreviousLocation($message, $success) {
	}
	public function _buildOverviewForm($state) {
		return $this->buildOverviewForm($state);
	}
	public function _resetProcess() {
		$this->resetProcess();
	}
	protected function getPlayerTitle() {
	}
	protected function getOverViewDescription() {
	}
	protected function getConfirmButtonLabel() {
	}
}

class TMS_Booking_PlayerTest extends PHPUnit_Framework_TestCase {
	public function test_getUserId() {
		$user_id = 42;
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$player = new BookingPlayerForTest([], 0, $user_id, $db);
		$this->assertEquals($user_id, $player->_getUserId());
	}

	public function test_getSortedSteps() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getApplicableSteps"])
			->disableOriginalConstructor()
			->getMock();

		$component1 = $this->createMock(Booking\Step::class);
		$component2 = $this->createMock(Booking\Step::class);
		$component3 = $this->createMock(Booking\Step::class);

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

		$player
			->expects($this->once())
			->method("getApplicableSteps")
			->willReturn([$component1, $component2, $component3]);

		$steps = $player->_getSortedSteps();

		$this->assertEquals([$component3, $component1, $component2], $steps);
	}

	public function test_getApplicableSteps() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getComponentsOfType", "getUserId"])
			->disableOriginalConstructor()
			->getMock();

		$user_id = 23;
		$player
			->expects($this->atLeast(1))
			->method("getUserId")
			->willReturn($user_id);

		$component1 = $this->createMock(Booking\Step::class);
		$component2 = $this->createMock(Booking\Step::class);
		$component3 = $this->createMock(Booking\Step::class);

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

		$player
			->expects($this->once())
			->method("getComponentsOfType")
			->with(Booking\Step::class)
			->willReturn([$component1, $component2, $component3]);

		$steps = $player->_getApplicableSteps();

		$this->assertEquals([$component1, $component3], $steps);
	}

	public function test_getProcessState_existing() {
		$course_id = 42;
		$user_id = 23;
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$state = $this->getMockBuilder(Booking\ProcessState::class)
			->disableOriginalConstructor()
			->getMock();
		$player = new BookingPlayerForTest([], $course_id, $user_id, $db);

		$db
			->expects($this->once())
			->method("load")
			->with($course_id, $user_id)
			->willReturn($state);

		$state2 = $player->_getProcessState();
		$this->assertEquals($state, $state2);
	}

	public function test_getProcessState_new() {
		$course_id = 42;
		$user_id = 23;
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$player = new BookingPlayerForTest([], $course_id, $user_id, $db);

		$db
			->expects($this->once())
			->method("load")
			->with($course_id, $user_id)
			->willReturn(null);

		$state = $player->_getProcessState();
		$expected = new Booking\ProcessState($course_id, $user_id, 0);
		$this->assertEquals($expected, $state);
	}

	public function test_saveProcessState() {
		$course_id = 42;
		$user_id = 23;
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$player = new BookingPlayerForTest([], $course_id, $user_id, $db);
		$state = $this->createMock(Booking\ProcessState::class);

		$db
			->expects($this->once())
			->method("save")
			->with($state)
			->willReturn(null);

		$player->_saveProcessState($state);
	}

	public function test_deleteProcessState() {
		$course_id = 42;
		$user_id = 23;
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$player = new BookingPlayerForTest([], $course_id, $user_id, $db);
		$state = $this->createMock(Booking\ProcessState::class);

		$db
			->expects($this->once())
			->method("delete")
			->with($state)
			->willReturn(null);

		$player->_deleteProcessState($state);
	}

	public function test_process_form_building() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "getForm", "txt", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 1;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);
		$player_title = "Player";

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->atLeastOnce())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$player
			->expects($this->exactly(3))
			->method("txt")
			->withConsecutive(["previous"], ["next"], ["abort"])
			->will($this->onConsecutiveCalls("lng_previous", "lng_next", "lng_abort"));

		$form
			->expects($this->exactly(3))
			->method("addCommandButton")
			->withConsecutive
				( ["previous", "lng_previous"]
				, ["next", "lng_next"]
				, ["abort", "lng_abort"]
				);

		$step2
			->expects($this->once())
			->method("appendToStepForm")
			->with($form);

		$player->process();
	}

	public function test_process_data_not_ok() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "getForm", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 1;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);
		$player_title = "Player";

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->atLeastOnce())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$step2
			->expects($this->once())
			->method("appendToStepForm")
			->with($form);

		$post = ["foo" => "bar"];
		$form
			->expects($this->once())
			->method("setValuesByArray")
			->with($post);

		$form
			->expects($this->once())
			->method("checkInput")
			->willReturn(true);

		$step2
			->expects($this->once())
			->method("getData")
			->with($form)
			->willReturn(null);

		$player
			->expects($this->never())
			->method("saveProcessState");

		$html = "HTML OUTPUT STEP 2";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->process("next", $post);

		$this->assertEquals($html, $view);
	}

	public function test_process_form_not_ok() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "getForm", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 1;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);
		$player_title = "Player";

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->atLeastOnce())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$step2
			->expects($this->once())
			->method("appendToStepForm")
			->with($form);

		$post = ["foo" => "bar"];
		$form
			->expects($this->once())
			->method("setValuesByArray")
			->with($post);

		$form
			->expects($this->once())
			->method("checkInput")
			->willReturn(false);

		$step2
			->expects($this->never())
			->method("getData");

		$player
			->expects($this->never())
			->method("saveProcessState");

		$html = "HTML OUTPUT STEP 2";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->process("next", $post);

		$this->assertEquals($html, $view);
	}

	public function test_process_data_ok() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "getForm", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form_step2 = $this->createMock(\ilPropertyFormGUI::class);
		$form_step3 = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 1;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);
		$player_title = "Player";

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$step1
			->expects($this->never())
			->method($this->anything());

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->atLeastOnce())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->exactly(2))
			->method("getForm")
			->will($this->onConsecutiveCalls($form_step2, $form_step3));

		$player
			->expects($this->exactly(2))
			->method("getPlayerTitle")
			->willReturn($player_title);

		$step2
			->expects($this->once())
			->method("appendToStepForm")
			->with($form_step2);

		$post = ["foo" => "bar"];
		$form_step2
			->expects($this->once())
			->method("setValuesByArray")
			->with($post);

		$form_step2
			->expects($this->once())
			->method("checkInput")
			->willReturn(true);

		$data = ["bar" => "baz"];
		$step2
			->expects($this->once())
			->method("getData")
			->with($form_step2)
			->willReturn($data);

		$new_state = $state
			->withNextStep()
			->withStepData(1, $data);

		$player
			->expects($this->once())
			->method("saveProcessState")
			->with($new_state);

		$step3
			->expects($this->once())
			->method("appendToStepForm")
			->with($form_step3);

		$form_step3
			->expects($this->never())
			->method("setValuesByArray");

		$html = "HTML OUTPUT STEP 3";
		$form_step3
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->process("next", $post);

		$this->assertEquals($html, $view);
	}

	public function test_process_build_only_on_no_post() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "getForm", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 1;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);
		$player_title = "Player";

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->once())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$step2
			->expects($this->once())
			->method("appendToStepForm")
			->with($form);

		$step2
			->expects($this->never())
			->method("getData");

		$step2
			->expects($this->never())
			->method("addDataToForm");

		$player
			->expects($this->never())
			->method("saveProcessState");

		$html = "HTML OUTPUT";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->process();

		$this->assertEquals($html, $view);
	}

	public function test_process_first() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "getForm", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 0;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);
		$player_title = "Player";

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$step2
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->atLeastOnce())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$step1
			->expects($this->once())
			->method("appendToStepForm")
			->with($form);

		$player
			->expects($this->never())
			->method("saveProcessState");

		$html = "HTML OUTPUT";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->process();

		$this->assertEquals($html, $view);
	}

	public function test_process_last() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "buildOverviewForm", "getForm", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form_step3 = $this->createMock(\ilPropertyFormGUI::class);
		$overview_form = $this->createMock(\ilPropertyFormGUI::class);
		$player_title = "Player";

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 2;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->atLeastOnce())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form_step3);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$step3
			->expects($this->once())
			->method("appendToStepForm")
			->with($form_step3);

		$post = ["foo" => "bar"];
		$form_step3
			->expects($this->once())
			->method("setValuesByArray")
			->with($post);

		$form_step3
			->expects($this->once())
			->method("checkInput")
			->willReturn(true);

		$data3 = "DATA 3";
		$step3
			->expects($this->once())
			->method("getData")
			->with($form_step3)
			->willReturn($data3);

		$new_state = $state
			->withNextStep()
			->withStepData(2, $data3);

		$player
			->expects($this->once())
			->method("saveProcessState")
			->with($new_state);

		$player
			->expects($this->once())
			->method("buildOverviewForm")
			->with($new_state)
			->willReturn($overview_form);

		$html = "HTML OUTPUT";
		$overview_form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->process("next", $post);

		$this->assertEquals($html, $view);
	}

	public function test_buildOverviewForm() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "getForm", "txt", "getPlayerTitle", "getOverViewDescription", "getConfirmButtonLabel"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 3;
		$data1 = "DATA 1";
		$data2 = "DATA 2";
		$data3 = "DATA 3";
		$state = (new Booking\ProcessState($crs_id, $usr_id, $step_number))
			->withStepData(0, $data1)
			->withStepData(1, $data2)
			->withStepData(2, $data3);
		$player_title = "Player";
		$overview_description = "This is the overview";
		$confirm_label = "Confirm";

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$player
			->expects($this->atLeastOnce())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$player
			->expects($this->once())
			->method("getConfirmButtonLabel")
			->willReturn($confirm_label);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$player
			->expects($this->once())
			->method("getOverViewDescription")
			->willReturn($overview_description);

		$step1
			->expects($this->once())
			->method("appendToOverviewForm")
			->with($data1, $form);
		$label1 = "LABEL 1";
		$step1
			->expects($this->once())
			->method("getLabel")
			->willReturn($label1);

		$step2
			->expects($this->once())
			->method("appendToOverviewForm")
			->with($data2, $form);
		$label2 = "LABEL 2";
		$step2
			->expects($this->once())
			->method("getLabel")
			->willReturn($label2);

		$step3
			->expects($this->once())
			->method("appendToOverviewForm")
			->with($data3, $form);
		$label3 = "LABEL 3";
		$step3
			->expects($this->once())
			->method("getLabel")
			->willReturn($label3);

		$form
			->expects($this->exactly(3))
			->method("addItem")
			->withConsecutive
				([$this->callback(function($item) use ($label1) {
						return ($item instanceof \ilFormSectionHeaderGUI) && ($item->getTitle() == $label1);
					})]
				,[$this->callback(function($item) use ($label2) {
						return ($item instanceof \ilFormSectionHeaderGUI) && ($item->getTitle() == $label2);
					})]
				,[$this->callback(function($item) use ($label3) {
						return ($item instanceof \ilFormSectionHeaderGUI) && ($item->getTitle() == $label3);
					})]
				);

		$player
			->expects($this->exactly(2))
			->method("txt")
			->withConsecutive(["previous"], ["abort"])
			->will($this->onConsecutiveCalls("lng_previous", "lng_abort"));

		$form
			->expects($this->exactly(3))
			->method("addCommandButton")
			->withConsecutive
				( ["previous", "lng_previous"]
				, ["confirm", $confirm_label]
				, ["abort", "lng_abort"]
				);

		$form2 = $player->_buildOverviewForm($state);
		$this->assertSame($form, $form2);
	}

	public function test_process_reset() {
		$db = $this->createMock(Booking\ProcessStateDB::class);
		$crs_id = 42;
		$usr_id = 23;
		$player = new BookingPlayerForTest([], $crs_id, $usr_id, $db);
		$state = $this->createMock(Booking\ProcessState::class);

		$db
			->expects($this->once())
			->method("load")
			->with($crs_id, $usr_id)
			->willReturn($state);
		$db
			->expects($this->once())
			->method("delete")
			->with($state);

		$player->_resetProcess();
	}

	public function test_process_start() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getProcessState", "resetProcess", "processStep"])
			->disableOriginalConstructor()
			->getMock();

		$state = $this->createMock(Booking\ProcessState::class);

		$player
			->expects($this->once())
			->method("resetProcess")
			->willReturn($state);


		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$post = ["foo" => "bar"];
		$view = "VIEW";
		$player
			->expects($this->once())
			->method("processStep")
			->with($state, $post)
			->willReturn($view);

		$view2 = $player->process("start", $post);
		$this->assertEquals($view, $view2);

	}

	public function test_process_abort() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getProcessState", "deleteProcessState", "redirectToPreviousLocation", "txt"])
			->disableOriginalConstructor()
			->getMock();

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 2;
		$state = new Booking\ProcessState($crs_id, $usr_id, $step_number);

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->once())
			->method("deleteProcessState")
			->willReturn($state);

		$player
			->expects($this->once())
			->method("txt")
			->with("aborted")
			->willReturn("lng_aborted");

		$player
			->expects($this->once())
			->method("redirectToPreviousLocation")
			->with(["lng_aborted"], false);

		$no_view = $player->process("abort", []);
		$this->assertNull($no_view);
	}

	public function test_process_confirm() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "deleteProcessState", "redirectToPreviousLocation", "txt", "getEntityRefId", "getUserId"])
			->disableOriginalConstructor()
			->getMock();

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 3;
		$data1 = "DATA 1";
		$data2 = "DATA 2";
		$data3 = "DATA 3";
		$state = (new Booking\ProcessState($crs_id, $usr_id, $step_number))
			->withStepData(0, $data1)
			->withStepData(1, $data2)
			->withStepData(2, $data3);

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->atLeastOnce())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->atLeastOnce())
			->method("getEntityRefId")
			->willReturn($crs_id);

		$player
			->expects($this->atLeastOnce())
			->method("getUserId")
			->willReturn($usr_id);

		$conf1 = "CONFIRMATION 1";
		$step1
			->expects($this->once())
			->method("processStep")
			->with($crs_id, $usr_id, $data1)
			->willReturn($conf1);
		$step2
			->expects($this->once())
			->method("processStep")
			->with($crs_id, $usr_id, $data2)
			->willReturn(null);
		$conf3 = "CONFIRMATION 3";
		$step3
			->expects($this->once())
			->method("processStep")
			->with($crs_id, $usr_id, $data3)
			->willReturn($conf3);

		$player
			->expects($this->once())
			->method("deleteProcessState")
			->willReturn($state);

		$player
			->expects($this->once())
			->method("redirectToPreviousLocation", true)
			->with([$conf1, $conf3]);

		$no_view = $player->process("confirm", []);
		$this->assertNull($no_view);
	}

	public function test_process_previous() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "getForm", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form_step1 = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 2;
		$data0 = "DATA 0";
		$data1 = array("foo" => "bar");
		$data2 = "DATA 2";
		$state = (new Booking\ProcessState($crs_id, $usr_id, $step_number))
			->withStepData(0, $data0)
			->withStepData(1, $data1)
			->withStepData(2, $data2);
		$player_title = "Player";
		$html = "HTML OUTPUT STEP 2";

		$step0 = $this->createMock(Booking\Step::class);
		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);

		$step0
			->expects($this->never())
			->method($this->anything());

		$step2
			->expects($this->never())
			->method($this->anything());

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->once())
			->method("getSortedSteps")
			->willReturn([$step0, $step1, $step2]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form_step1);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$player
			->expects($this->once())
			->method("saveProcessState");

		$step1
			->expects($this->once())
			->method("appendToStepForm")
			->with($form_step1);

		$step1
			->expects($this->once())
			->method("addDataToForm")
			->with($form_step1, $data1);

		$form_step1
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->process("previous", $post);

		$this->assertEquals($html, $view);
	}

	public function test_process_build_only_on_no_post_with_saved_data() {
		$player = $this->getMockBuilder(BookingPlayerForTest::class)
			->setMethods(["getSortedSteps", "getProcessState", "saveProcessState", "getForm", "getPlayerTitle"])
			->disableOriginalConstructor()
			->getMock();

		$form = $this->createMock(\ilPropertyFormGUI::class);

		$crs_id = 23;
		$usr_id = 42;
		$step_number = 1;
		$data0 = "DATA 0";
		$data1 = array("foo" => "bar");
		$data2 = "DATA 2";
		$state = (new Booking\ProcessState($crs_id, $usr_id, $step_number))
			->withStepData(0, $data0)
			->withStepData(1, $data1)
			->withStepData(2, $data2);
		$player_title = "Player";

		$step1 = $this->createMock(Booking\Step::class);
		$step2 = $this->createMock(Booking\Step::class);
		$step3 = $this->createMock(Booking\Step::class);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

		$player
			->expects($this->once())
			->method("getProcessState")
			->willReturn($state);

		$player
			->expects($this->once())
			->method("getSortedSteps")
			->willReturn([$step1, $step2, $step3]);

		$player
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$player
			->expects($this->once())
			->method("getPlayerTitle")
			->willReturn($player_title);

		$step2
			->expects($this->once())
			->method("appendToStepForm")
			->with($form);

		$step2
			->expects($this->never())
			->method("getData");

		$step2
			->expects($this->once())
			->method("addDataToForm")
			->with($form, $data1);

		$player
			->expects($this->never())
			->method("saveProcessState");

		$html = "HTML OUTPUT";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $player->process();

		$this->assertEquals($html, $view);
	}
}
