<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

use ILIAS\TMS\Wizard;

require_once(__DIR__."/../../../Services/Form/classes/class.ilPropertyFormGUI.php");

class _WizardPlayer extends Wizard\Player {
	public function _getState() {
		return $this->getState();
	}
	public function _buildOverviewForm(Wizard\State $state) {
		return $this->buildOverviewForm($state);
	}
}

class TMS_Wizard_PlayerTest extends PHPUnit_Framework_TestCase {
	static protected $count_setups = 0;

	public function setUp() {
		$this->ilias_bindings = $this->createMock(Wizard\ILIASBindings::class);
		$this->wizard = $this->createMock(Wizard\Wizard::class);
		$this->state_db = $this->createMock(Wizard\StateDB::class);
		$this->player = new _WizardPlayer($this->ilias_bindings, $this->wizard, $this->state_db);
		$this->step_count = 0;
		$this->form_count = 0;

		$this->wizard_id = "wfid_".self::$count_setups;
		self::$count_setups++;

		$this->wizard
			->expects($this->atLeastOnce())
			->method("getId")
			->willReturn($this->wizard_id);
	}

	public function createStepMock() {
		$this->step_count++;
		return $this->getMockBuilder(Wizard\Step::class)
			->disableOriginalConstructor()
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMockClassName("WizardStep".$this->step_count)
			->getMock();		
	}


	public function createFormMock() {
		$this->form_count++;
		return $this->getMockBuilder(ilPropertyFormGUI::class)
			->disableOriginalConstructor()
			->disableOriginalClone()
			->disableArgumentCloning()
			->disallowMockingUnknownTypes()
			->setMockClassName("Form".$this->step_count)
			->getMock();		
	}

	public function test_getState_existing() {
		$state = $this->getMockBuilder(Wizard\State::class)
			->disableOriginalConstructor()
			->getMock();

		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$state2 = $this->player->_getState();
		$this->assertEquals($state, $state2);
	}

	public function test_getState_new() {
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn(null);

		$state = $this->player->_getState();
		$expected = new Wizard\State($this->wizard_id, 0);
		$this->assertEquals($expected, $state);
	}

	public function test_process_form_building() {
		$step_number = 1;
		$state = new Wizard\State($this->wizard_id, $step_number);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->once())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$form = $this->createFormMock();
		$this->ilias_bindings
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$this->ilias_bindings
			->expects($this->exactly(4))
			->method("txt")
			->withConsecutive(["previous"], ["next"], ["abort"], ["title"])
			->will($this->onConsecutiveCalls("lng_previous", "lng_next", "lng_abort", "lng_title"));

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

		$this->player->run();
	}

	public function test_process_data_not_ok() {
		$step_number = 1;
		$state = new Wizard\State($this->wizard_id, $step_number);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->once())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$form = $this->createFormMock();
		$this->ilias_bindings
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

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

		$this->state_db
			->expects($this->never())
			->method("save");

		$html = "HTML OUTPUT STEP 2";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $this->player->run("next", $post);

		$this->assertEquals($html, $view);
	}

	public function test_process_form_not_ok() {
		$step_number = 1;
		$state = new Wizard\State($this->wizard_id, $step_number);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->once())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$form = $this->createFormMock();
		$this->ilias_bindings
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());
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

		$this->state_db
			->expects($this->never())
			->method("save");

		$html = "HTML OUTPUT STEP 2";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $this->player->run("next", $post);

		$this->assertEquals($html, $view);
	}

	public function test_process_data_ok() {
		$step_number = 1;
		$state = new Wizard\State($this->wizard_id, $step_number);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->atLeastOnce())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$form_step2 = $this->createFormMock();
		$form_step3 = $this->createFormMock();
		$this->ilias_bindings
			->expects($this->exactly(2))
			->method("getForm")
			->will($this->onConsecutiveCalls($form_step2, $form_step3));

		$step1
			->expects($this->never())
			->method($this->anything());

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

		$this->state_db
			->expects($this->once())
			->method("save")
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

		$view = $this->player->run("next", $post);

		$this->assertEquals($html, $view);
	}

	public function test_process_build_only_on_no_post() {
		$step_number = 1;
		$state = new Wizard\State($this->wizard_id, $step_number);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->once())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$form = $this->createFormMock();
		$this->ilias_bindings
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

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

		$this->state_db
			->expects($this->never())
			->method("save");

		$html = "HTML OUTPUT";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $this->player->run();

		$this->assertEquals($html, $view);
	}
	public function test_process_first() {
		$step_number = 0;
		$state = new Wizard\State($this->wizard_id, $step_number);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->once())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$form = $this->createFormMock();
		$this->ilias_bindings
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

		$step2
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

		$step1
			->expects($this->once())
			->method("appendToStepForm")
			->with($form);

		$this->state_db
			->expects($this->never())
			->method("save");

		$html = "HTML OUTPUT";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $this->player->run();

		$this->assertEquals($html, $view);
	}

	public function test_process_last() {
		$this->player = $this->getMockBuilder(_WizardPlayer::class)
			->setMethods(["buildOverviewForm"])
			->setConstructorArgs([$this->ilias_bindings, $this->wizard, $this->state_db])
			->getMock();

		$step_number = 2;
		$state = new Wizard\State($this->wizard_id, $step_number);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->atLeastOnce())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$form_step3 = $this->createFormMock();
		$overview_form= $this->createFormMock();
		$this->ilias_bindings
			->expects($this->once())
			->method("getForm")
			->will($this->onConsecutiveCalls($form_step3, $overview_form));

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

		$this->state_db
			->expects($this->once())
			->method("save")
			->with($new_state);

		$this->player
			->expects($this->once())
			->method("buildOverviewForm")
			->with($new_state)
			->willReturn($overview_form);

		$html = "HTML OUTPUT";
		$overview_form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $this->player->run("next", $post);

		$this->assertEquals($html, $view);
	}

	public function test_buildOverviewForm() {
		// to satisfy assertion that method is invoked, which
		// is not required in this testcase
		$this->wizard->getId();

		$step_number = 3;
		$data1 = "DATA 1";
		$data2 = "DATA 2";
		$data3 = "DATA 3";
		$state = (new Wizard\State($this->wizard_id, $step_number))
			->withStepData(0, $data1)
			->withStepData(1, $data2)
			->withStepData(2, $data3);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->once())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$form= $this->createFormMock();
		$this->ilias_bindings
			->expects($this->exactly(1))
			->method("getForm")
			->willReturn($form);

		$step1
			->expects($this->once())
			->method("appendToOverviewForm")
			->with($form, $data1);
		$label1 = "LABEL 1";
		$step1
			->expects($this->once())
			->method("getLabel")
			->willReturn($label1);

		$step2
			->expects($this->once())
			->method("appendToOverviewForm")
			->with($form, $data2);
		$label2 = "LABEL 2";
		$step2
			->expects($this->once())
			->method("getLabel")
			->willReturn($label2);

		$step3
			->expects($this->once())
			->method("appendToOverviewForm")
			->with($form, $data3);
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

		$this->ilias_bindings
			->expects($this->exactly(5))
			->method("txt")
			->withConsecutive(["previous"], ["confirm"], ["abort"], ["title"], ["overview_description"])
			->will($this->onConsecutiveCalls("lng_previous", "lng_confirm", "lng_abort", "lng_title", "lng_overview_description"));

		$form
			->expects($this->exactly(3))
			->method("addCommandButton")
			->withConsecutive
				( ["previous", "lng_previous"]
				, ["confirm", "lng_confirm"]
				, ["abort", "lng_abort"]
				);

		$form2 = $this->player->_buildOverviewForm($state);
		$this->assertSame($form, $form2);
	}

	public function test_process_start() {
		$this->player = $this->getMockBuilder(_WizardPlayer::class)
			->setMethods(["runStep"])
			->setConstructorArgs([$this->ilias_bindings, $this->wizard, $this->state_db])
			->getMock($this->ilias_bindings, $this->wizard, $this->state_db);

		$state = new Wizard\State($this->wizard_id, 0);

		$this->state_db
			->expects($this->atLeastOnce())
			->method("load")
			->with($this->wizard_id)
			->willReturn(null);

		$this->state_db
			->expects($this->once())
			->method("delete")
			->with($state);

		$post = ["foo" => "bar"];
		$view = "VIEW";
		$this->player
			->expects($this->once())
			->method("runStep")
			->with($state, $post)
			->willReturn($view);

		$view2 = $this->player->run("start", $post);
		$this->assertEquals($view, $view2);
	}

	public function test_process_abort() {
		$step_number = 2;
		$state = new Wizard\State($this->wizard_id, $step_number);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$this->state_db
			->expects($this->once())
			->method("delete")
			->willReturn($state);

		$this->ilias_bindings
			->expects($this->once())
			->method("txt")
			->with("aborted")
			->willReturn("lng_aborted");

		$this->ilias_bindings
			->expects($this->once())
			->method("redirectToPreviousLocation")
			->with(["lng_aborted"], false);

		$no_view = $this->player->run("abort", []);
		$this->assertNull($no_view);
	}

	public function test_process_confirm() {
		$step_number = 3;
		$data1 = "DATA 1";
		$data2 = "DATA 2";
		$data3 = "DATA 3";
		$state = (new Wizard\State($this->wizard_id, $step_number))
			->withStepData(0, $data1)
			->withStepData(1, $data2)
			->withStepData(2, $data3);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->once())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$conf1 = "CONFIRMATION 1";
		$step1
			->expects($this->once())
			->method("processStep")
			->with($data1)
			->willReturn($conf1);
		$step2
			->expects($this->once())
			->method("processStep")
			->with($data2)
			->willReturn(null);
		$conf3 = "CONFIRMATION 3";
		$step3
			->expects($this->once())
			->method("processStep")
			->with($data3)
			->willReturn($conf3);

		$this->state_db
			->expects($this->once())
			->method("delete")
			->willReturn($state);

		$this->wizard
			->expects($this->once())
			->method("finish");

		$this->ilias_bindings
			->expects($this->once())
			->method("redirectToPreviousLocation")
			->with([$conf1, $conf3], true);

		$no_view = $this->player->run("confirm", []);
		$this->assertNull($no_view);
	}

	public function test_process_previous() {
		$step_number = 2;
		$data0 = "DATA 0";
		$data1 = array("foo" => "bar");
		$data2 = array("data" => "DATA 2");
		$state = (new Wizard\State($this->wizard_id, $step_number))
			->withStepData(0, $data0)
			->withStepData(1, $data1)
			->withStepData(2, $data2);

		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state)
		;

		$step0 = $this->createStepMock();
		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$this->wizard
			->expects($this->exactly(2))
			->method("getSteps")
			->willReturn([$step0, $step1, $step2])
		;

		$form_step1 = $this->createMock(\ilPropertyFormGUI::class);
		$form_step2 = $this->createMock(\ilPropertyFormGUI::class);

		$form_step2
			->expects($this->once())
			->method("checkInput")
			->will($this->returnValue(true))
		;

		$step0
			->expects($this->never())
			->method($this->anything())
		;

		$step2
			->expects($this->once())
			->method("getData")
			->will($this->returnValue($data2))
		;

		$this->ilias_bindings
			->expects($this->exactly(2))
			->method("getForm")
			->will($this->onConsecutiveCalls($form_step2, $form_step1))
		;

		$this->state_db
			->expects($this->exactly(2))
			->method("save")
			->withConsecutive(array($state), array($state->withPreviousStep()))
		;

		$step1
			->expects($this->once())
			->method("appendToStepForm")
			->with($form_step1)
		;

		$step1
			->expects($this->once())
			->method("addDataToForm")
			->with($form_step1, $data1)
		;

		$html = "HTML";
		$form_step1
			->expects($this->once())
			->method("getHTML")
			->willReturn($html)
		;

		$view = $this->player->run("previous", $data2);

		$this->assertEquals($html, $view);
	}

	public function test_process_build_only_on_no_post_with_saved_data() {
		$step_number = 1;
		$data0 = "DATA 0";
		$data1 = array("foo" => "bar");
		$data2 = "DATA 2";
		$state = (new Wizard\State($this->wizard_id, $step_number))
			->withStepData(0, $data0)
			->withStepData(1, $data1)
			->withStepData(2, $data2);
		$this->state_db
			->expects($this->once())
			->method("load")
			->with($this->wizard_id)
			->willReturn($state);

		$step1 = $this->createStepMock();
		$step2 = $this->createStepMock();
		$step3 = $this->createStepMock();
		$this->wizard
			->expects($this->once())
			->method("getSteps")
			->willReturn([$step1, $step2, $step3]);

		$step1
			->expects($this->never())
			->method($this->anything());
		$step3
			->expects($this->never())
			->method($this->anything());

		$form = $this->createFormMock();
		$this->ilias_bindings
			->expects($this->once())
			->method("getForm")
			->willReturn($form);

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

		$this->state_db
			->expects($this->never())
			->method("save");

		$html = "HTML OUTPUT";
		$form
			->expects($this->once())
			->method("getHTML")
			->willReturn($html);

		$view = $this->player->run();

		$this->assertEquals($html, $view);
	}
}
