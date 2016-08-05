<?php

class ilManualAssessmentSettingsGUI {
	public function __construct($a_parent_gui, $a_ref_id) {
		global $tpl, $ilCtrl, $ilAccess, $ilToolbar, $ilLocator, $tree, $lng, $ilLog, $ilias;
		$this->ctrl = $ilCtrl;
		$this->parent_gui = $a_parent_gui;
		$this->ref_id = $a_ref_id;
		$this->parent_gui = $a_parent_gui;
		$this->tpl = $tpl;
	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		switch($cmd) {
			case "view":
			$form = $this->initSettingsForm();
			$this->tpl->setValuesByArray($this->parent_gui->getEditFormValues());
		}
	}

	protected function initSettingsForm() {
		$form = $this->parent_gui->initEditForm();
	}

}