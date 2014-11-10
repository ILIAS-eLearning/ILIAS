<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once('./Services/Object/classes/class.ilObjectGUI.php');
include_once('./Modules/Bibliographic/classes/Admin/class.ilObjBibliographicAdminTableGUI.php');
include_once('./Modules/Bibliographic/classes/Admin/class.ilBibliographicSetting.php');
include_once('./Modules/Bibliographic/classes/Admin/class.ilObjBibliographicAdminLibrariesGUI.php');
include_once('./Modules/Bibliographic/classes/Admin/class.ilObjBibliographicAdminLibrariesFormGUI.php');

/**
 * Bibliographic Administration Settings.
 *
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilObjBibliographicAdminGUI: ilPermissionGUI, ilObjBibliographicAdminLibrariesGUI, ilObjBibliographicAdminAttributeOrderGUI
 *
 * @ingroup      ModulesBibliographic
 */
class ilObjBibliographicAdminGUI extends ilObjectGUI {

	/**
	 * @param      $a_data
	 * @param      $a_id
	 * @param bool $a_call_by_reference
	 * @param bool $a_prepare_output
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true) {
		$this->type = 'bibs';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
		$this->lng->loadLanguageModule('bibl');
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
			case 'ilpermissiongui':
				$this->prepareOutput();
				$this->tabs_gui->setTabActive('perm_settings');
				include_once('Services/AccessControl/classes/class.ilPermissionGUI.php');
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;
			default:
				$this->prepareOutput();
				$ilObjBibliographicAdminLibrariesGUI = new ilObjBibliographicAdminLibrariesGUI($this);
				$this->ctrl->forwardCommand($ilObjBibliographicAdminLibrariesGUI);
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
				'ilObjBibliographicAdminGUI',
				'ilObjBibliographicAdminLibrariesGUI'
			), 'view'));
		}
		if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
			$this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), array(), 'ilpermissiongui');
		}
	}
}