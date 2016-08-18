<?php

class ilManualAssessmentSettingsGUI {

	const PROP_CONTENT = "content";
	const PROP_RECORD_TEMPLATE = "record_template";
	const PROP_TITLE = "title";
	const PROP_DESCRIPTION = "description";

	public function __construct($a_parent_gui, $a_ref_id) {
		global $DIC;
		$this->ctrl = $DIC['ilCtrl'];
		$this->parent_gui = $a_parent_gui;
		$this->object = $a_parent_gui->object;
		$this->ref_id = $a_ref_id;
		$this->tpl = $DIC['tpl'];
		$this->lng = $DIC['lng'];
	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case "edit":
			case "update":
			case "cancel":
				if(!$this->object->accessHandler()->checkAccessToObj($this->object,'write')) {
					$this->parent_gui->handleAccessViolation();
				}
				$this->$cmd();
			break;
		}
	}


	protected function cancel() {
		$this->ctrl->redirect($this->parent_gui);
	}

	protected function edit() {
		$form = $this->fillForm($this->initSettingsForm()
					,$this->object
					,$this->object->getSettings());
		$this->renderForm($form);
	}

	protected function renderForm(ilPropertyFormGUI $a_form) {
		$this->tpl->setContent($a_form->getHTML());
	}

	protected function update() {
		$form = $this->initSettingsForm();
		$form->setValuesByArray($_POST);
		if($form->checkInput()) {
			$this->object->setTitle($_POST[self::PROP_TITLE]);
			$this->object->setDescription($_POST[self::PROP_DESCRIPTION]);
			$this->object->getSettings()->setContent($_POST[self::PROP_CONTENT])
								->setRecordTemplate($_POST[self::PROP_RECORD_TEMPLATE]);
			$this->object->update();
		}
		$this->renderForm($form);
	}


	protected function initSettingsForm() {
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('mass_edit'));

		// title
		$ti = new ilTextInputGUI($this->lng->txt('title'), self::PROP_TITLE);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt('description'), self::PROP_DESCRIPTION);
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);


		$item = new ilTextAreaInputGUI($this->lng->txt('mass_content'), self::PROP_CONTENT);
		$form->addItem($item);
		$item = new ilTextAreaInputGUI($this->lng->txt('mass_record_template'), self::PROP_RECORD_TEMPLATE);
		$form->addItem($item);
		$form->addCommandButton('update', $this->lng->txt('save'));
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));
		return $form;
	}
	protected function fillForm(ilPropertyFormGUI $a_form, ilObjManualAssessment $mass, ilManualAssessmentSettings $settings) {
		$a_form->setValuesByArray(array(
			  self::PROP_TITLE => $mass->getTitle()
			, self::PROP_DESCRIPTION => $mass->getDescription()
			, self::PROP_CONTENT => $settings->content()
			, self::PROP_RECORD_TEMPLATE => $settings->recordTemplate()
			));
		return $a_form;
	}
}