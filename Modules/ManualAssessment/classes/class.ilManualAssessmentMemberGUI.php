<?php

require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';
//require_once 'Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php';
require_once 'Services/Form/classes/class.ilNonEditableValueGUI.php';
require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
require_once 'Modules/ManualAssessment/classes/LearningProgress/class.ilManualAssessmentLPInterface.php';
/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the ManualAssessment is used.
 * It caries a LPStatus, which is set manually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 */
class ilManualAssessmentMemberGUI {
		public function __construct($members_gui ,$a_parent_gui, $a_ref_id) {
		global $DIC;
		$this->ctrl = $DIC['ilCtrl'];
		$this->members_gui = $members_gui;
		$this->parent_gui = $a_parent_gui;
		$this->object = $a_parent_gui->object;
		$this->ref_id = $a_ref_id;
		$this->tpl =  $DIC['tpl'];
		$this->lng = $DIC['lng'];
		$this->toolbar = $DIC['ilToolbar'];
		$this->ctrl->saveParameter($this,'usr_id');
		$this->examinee = new ilObjUser($_GET['usr_id']);
		$this->examiner = $DIC['ilUser'];
		$this->setTabs($DIC['ilTabs']);
		$this->member = $this->object->membersStorage()
							->loadMember($this->object,$this->examinee);
	}

	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case "edit":
			case "save":
			case "finalize":
			case "cancel":
				$this->$cmd();
			break;
		}
	}

	protected function setTabs(ilTabsGUI $tabs) {
		$tabs->clearTargets();
		$tabs->setBackTarget($this->lng->txt('back'),
			$this->getBackLink());
	}

	protected function getBackLink() {
		return $this->ctrl->getLinkTargetByClass(
				array(get_class($this->parent_gui)
					,get_class($this->members_gui))
				,'view');
	}

	protected function cancel() {
		$this->ctrl->redirect($this->members_gui);
	}

	protected function finalize() {
		$form = $this->initGradingForm();
		$form->setValuesByArray($_POST);
		if($form->checkInput()) {
			$member = $this->updateDataInMemberByArray($this->member,$_POST);
			if($member->mayBeFinalized()) {
				$this->member = $member->withFinalized();
				$this->object->membersStorage()->updateMember($this->member);
				ilManualAssessmentLPInterface::updateLPStatusOfMember($member);
			} else {
				ilUtil::sendFailure('member may not be finalized');
			}
		}
		$this->renderForm($this->fillForm($this->initGradingForm(),$this->member));
	}

	protected function  edit() {
		$form = $this->fillForm($this->initGradingForm(),$this->member);
		$this->renderForm($form);
	}

	protected function renderForm(ilPropertyFormGUI $a_form) {
		$this->tpl->setContent($a_form->getHTML());
	}

	protected function save() {
		$form = $this->initGradingForm();
		$form->setValuesByArray($_POST);
		if($form->checkInput()) {
			$this->member = $this->updateDataInMemberByArray($this->member,$_POST);
			$this->object->membersStorage()->updateMember($this->member);
		}
		$this->renderForm($form);
	}

	protected function updateDataInMemberByArray(ilManualAssessmentMember $member, $data) {
		$member = $member->withRecord($data["record"])
					->withInternalNote($data["internal_note"])
					->withLPStatus($data["learning_progress"])
					->withNotify(($data["notify"]  == 1 ? 1 : 0));
		return $member;
	}

	protected function initGradingForm() {
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($this->object->getType()."_edit"));

		$non_editable = $this->member->finalized();
		$to_notify = $this->member->notify();

		$examinee_name = $this->examinee->getLastname().', '.$this->examinee->getFirstname();

		$usr_name = new ilNonEditableValueGUI($this->lng->txt('name'),'name');
		$form->addItem($usr_name);
		// record
		$ti = new ilTextAreaInputGUI($this->lng->txt("record"), "record");
		$ti->setCols(40);
		$ti->setRows(5);
		$ti->setDisabled($non_editable);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("internal_note"), "internal_note");
		$ta->setCols(40);
		$ta->setRows(5);
		$ta->setDisabled($non_editable);
		$form->addItem($ta);

		$learning_progress = new ilSelectInputGUI($this->lng->txt("LP"),"learning_progress");
		$learning_progress->setOptions(
			array(ilManualAssessmentMembers::LP_IN_PROGRESS => "--"
				, ilManualAssessmentMembers::LP_FAILED => "failed"
				, ilManualAssessmentMembers::LP_COMPLETED => "completed"));
		$learning_progress->setDisabled($non_editable);
		$form->addItem($learning_progress);

		// notify examinee
		$notify = new ilCheckboxInputGUI($this->lng->txt("notify"), "notify");
		$notify->setChecked($to_notify);
		$notify->setDisabled($non_editable);
		$form->addItem($notify);

		if(!$non_editable) {
			$form->addCommandButton("save", $this->lng->txt("save"));
			$form->addCommandButton("finalize",$this->lng->txt("finalize"));
		}
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		return $form;
	}

	protected function fillForm(ilPropertyFormGUI $a_form, ilManualAssessmentMember $member) {
		$a_form->setValuesByArray(array(
			  "name" => $member->name()
			, "record" => $member->record()
			, "internal_note" => $member->internalNote()
			, "notify" => $member->notify()
			, "learning_progress" => (int)$member->LPStatus()
			));
		return $a_form;
	}
}