<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Object/classes/class.ilObjectGUI.php';

/** 
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilObjECSSettingsGUI: ilPermissionGUI, ilECSSettingsGUI
*/
class ilObjECSSettingsGUI extends ilObjectGUI
{

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng;
		
		$this->type = 'cals';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng = $lng;
		$this->lng->loadLanguageModule('dateplaner');
		$this->lng->loadLanguageModule('jscalendar');
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $ilErr,$ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
			
			case 'ilecssettingsgui':
				$this->tabs_gui->setTabActive('settings');
				include_once './Services/WebServices/ECS/classes/class.ilECSSettingsGUI.php';
				$settings = new ilECSSettingsGUI();
				$this->ctrl->forwardCommand($settings);
				break;
			
			default:
				$this->tabs_gui->setTabActive('settings');
				include_once './Services/WebServices/ECS/classes/class.ilECSSettingsGUI.php';
				$settings = new ilECSSettingsGUI();
				$this->ctrl->setCmdClass('ilecssettingsgui');
				$this->ctrl->forwardCommand($settings);
				break;
		}
		return true;
	}
	

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		global $rbacsystem, $ilAccess;
 		if ($ilAccess->checkAccess("read",'',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTargetByClass('ilecssettingsgui', "overview"),
				array(),
				'ilecssettingsgui'
			);
		}
		if ($ilAccess->checkAccess('edit_permission','',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
					$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
					array(),'ilpermissiongui');
		}
	}
	
}
?>