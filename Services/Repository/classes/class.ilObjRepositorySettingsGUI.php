<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Repository settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjRepositorySettingsGUI: ilPermissionGUI
 *
 * @ingroup ServicesRepository
 */
class ilObjRepositorySettingsGUI extends ilObjectGUI
{
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{		
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->type = 'reps';
		$this->lng->loadLanguageModule('rep');
	}
	
	public function executeCommand()
	{
		global $ilErr, $ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess('write', '', $this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				$this->$cmd();				
				break;
		}
		return true;
	}	
	
	public function getAdminTabs(&$tabs_gui) 
	{
		$tabs_gui->addTab("settings",
			$this->lng->txt("settings"),
			$this->ctrl->getLinkTarget($this, "settings"));
		
		$tabs_gui->addTab("settings2",
			$this->lng->txt("settings"),
			$this->ctrl->getLinkTarget($this, "settings"));
	}
	
	public function view()
	{
					
	}
	
	protected function initSettingsForm()
	{
		
	}
	
	public function saveSettings()
	{
		
	}
}

?>