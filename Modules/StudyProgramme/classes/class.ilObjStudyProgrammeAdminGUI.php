<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once('./Services/Object/classes/class.ilObjectGUI.php');
include_once('./Modules/StudyProgramme/classes/types/class.ilStudyProgrammeTypeGUI.php');

/**
 * StudyProgramme Administration Settings.
 *
 * @author       Michael Herren <mh@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeAdminGUI: ilStudyProgrammeTypeGUI
 */
class ilObjStudyProgrammeAdminGUI extends ilObjectGUI {

	/**
	 * @param      $a_data
	 * @param      $a_id
	 * @param bool $a_call_by_reference
	 * @param bool $a_prepare_output
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true) {
		$this->type = 'prgs';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->lng->loadLanguageModule('prg');
		//Check Permissions globally for all SubGUIs. We only check write permissions
		$this->checkPermission('write');
	}


	/**
	 * @return bool|void
	 * @throws ilCtrlException
	 */
	public function executeCommand() {
		$next_class = $this->ctrl->getNextClass($this);
		switch ($next_class) {
			/*case 'ilpermissiongui':
				$this->prepareOutput();
				$this->tabs_gui->setTabActive('perm_settings');
				include_once('Services/AccessControl/classes/class.ilPermissionGUI.php');
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;*/
			default:
				$this->prepareOutput();
				$type_gui = new ilStudyProgrammeTypeGUI($this);
				$this->ctrl->forwardCommand($type_gui);
				break;
		}
	}


	public function getAdminTabs() {
		global $rbacsystem;
		/**
		 * @var $rbacsystem ilRbacSystem
		 */

		if ($rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTargetByClass(array(
				'ilObjStudyProgrammeAdminGUI',
				'ilStudyProgrammeTypeGUI'
			), 'listTypes'));
		}
		/*if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), array(), 'ilpermissiongui');
		}*/
	}
}