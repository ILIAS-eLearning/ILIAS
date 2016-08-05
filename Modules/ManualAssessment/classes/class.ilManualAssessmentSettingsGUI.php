<?php

class ilManualAssessmentSettingsGUI {

	const PROP_CONTENT = "content";
	const PROP_RECORD_TEMPLATE = "record_template";
	const PROP_TITLE = "title";
	const PROP_DESCRIPTION = "description";

	public function __construct($a_parent_gui, $a_ref_id) {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias;
		$this->ctrl = $ilCtrl;
		$this->parent_gui = $a_parent_gui;
		$this->object = $a_parent_gui->object;
		$this->ref_id = $a_ref_id;
		$this->tpl = $tpl;
		$this->lng = $lng;
	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case "view":
			case "update":
			case "cancel":
				$this->$cmd();
			break;
		}
	}


	protected function cancel() {
		$this->ctrl->redirect($this->parent_gui);
	}

	protected function view() {
		$form = $this->fillForm($this->initSettingsForm()
					,$this->object
					,$this->object->loadSettings());
		$this->renderForm($form);
	}

	protected function renderForm(ilPropertyFormGUI $a_form) {
		$this->tpl->setContent($a_form->getHTML());
	}

	protected function update() {
		$form = $this->initSettingsForm();
		$form->setValuesByArray($_POST);
		if($form->checkInput()) {
			$settings = new ilManualAssessmentSettings($this->object);
			$settings = $settings->withContent($_POST[self::PROP_CONTENT])
								->withRecordTemplate($_POST[self::PROP_RECORD_TEMPLATE]);
			$this->object->updateSettings($settings);
			$this->object->update();
		}
		$this->renderForm($form);
	}


	protected function initSettingsForm() {
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt($this->object->getType()."_edit"));

		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), self::PROP_TITLE);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);

		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), self::PROP_DESCRIPTION);
		$ta->setCols(40);
		$ta->setRows(2);
		$form->addItem($ta);


		$item = new ilTextAreaInputGUI($this->lng->txt("content"), self::PROP_CONTENT);
		$form->addItem($item);
		$item = new ilTextAreaInputGUI($this->lng->txt("record_template"), self::PROP_RECORD_TEMPLATE);
		$form->addItem($item);
		$form->addCommandButton("update", $this->lng->txt("save"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		return $form;
	}
	protected function fillForm(ilPropertyFormGUI $a_form,ilObjManualAssessment $mass, ilManualAssessmentSettings $settings) {
		$a_form->setValuesByArray(array
			( 'title' => $mass->getTitle()
			, 'description' => $mass->getDescription()
			, self::PROP_CONTENT => $settings->content()
			, self::PROP_RECORD_TEMPLATE => $settings->recordTemplate()
			));
		return $a_form;
	}
}