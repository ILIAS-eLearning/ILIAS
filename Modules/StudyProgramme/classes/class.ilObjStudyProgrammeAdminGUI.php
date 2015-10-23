<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once('./Services/Object/classes/class.ilObjectGUI.php');
include_once('./Modules/StudyProgramme/classes/types/class.ilStudyProgrammeTypeGUI.php');

/**
 * StudyProgramme Administration Settings.
 *
 * @author       Michael Herren <mh@studer-raimann.ch>
 * @author       Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeAdminGUI: ilStudyProgrammeTypeGUI
 * @ilCtrl_Calls ilObjStudyProgrammeAdminGUI: ilPermissionGUI
 */
class ilObjStudyProgrammeAdminGUI extends ilObjectGUI {

	/**
	 * @param      $a_data
	 * @param      $a_id
	 * @param bool $a_call_by_reference
	 * @param bool $a_prepare_output
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true) {
		global $ilCtrl;
		$this->ctrl = $ilCtrl;
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
		$this->prepareOutput();
		switch ($next_class) {
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once('Services/AccessControl/classes/class.ilPermissionGUI.php');
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			default:
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
			$this->tabs_gui->addTarget('prg_subtypes', $this->ctrl->getLinkTargetByClass(array(
				'ilObjStudyProgrammeAdminGUI',
				'ilStudyProgrammeTypeGUI'
			), 'listTypes'));
		}
		if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), array(), 'ilpermissiongui');
		}
	}

	public function _goto($ref_id) {
		$this->ctrl->initBaseClass("ilAdministrationGUI");
		$this->ctrl->setTargetScript("ilias.php");
		$this->ctrl->setParameterByClass("ilObjStudyProgrammeAdminGUI", "ref_id", $ref_id);
		$this->ctrl->setParameterByClass("ilObjStudyProgrammeAdminGUI", "admin_mode", "settings");
		$this->ctrl->redirectByClass(array( "ilAdministrationGUI", "ilObjStudyProgrammeAdminGUI" ), "view");
	}
}