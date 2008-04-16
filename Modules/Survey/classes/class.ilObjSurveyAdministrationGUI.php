<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjSurveyAdministrationGUI
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjSurveyAdministrationGUI: ilPermissionGUI
*
* @extends ilObjectGUI
*/
class ilObjSurveyAdministrationGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	var $conditions;

	function ilObjSurveyAdministrationGUI($a_data,$a_id,$a_call_by_reference)
	{
		global $rbacsystem;

		$this->type = "svyf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		if (!$rbacsystem->checkAccess('read',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read_assf"),$this->ilias->error_obj->WARNING);
		}
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == "" || $cmd == "view")
				{
					$cmd = "settings";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}


	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff

		// always send a message
		ilUtil::sendInfo($this->lng->txt("object_added"),true);

		$this->ctrl->redirect($this);
	}


	/**
	* display survey settings form
	*/
	/**
	* Default settings tab for Survey settings
	*
	* @access	public
	*/
	function settingsObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl, $tpl;
		
		$surveySetting = new ilSetting("survey");
		$unlimited_invitation = array_key_exists("unlimited_invitation", $_GET) ? $_GET["unlimited_invitation"] : $surveySetting->get("unlimited_invitation");
		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("survey_defaults"));
		
		// unlimited invitation
		$enable = new ilCheckboxInputGUI($lng->txt("survey_unlimited_invitation"), "unlimited_invitation");
		$enable->setChecked($unlimited_invitation);
		$enable->setInfo($lng->txt("survey_unlimited_invitation_desc"));
		$form->addItem($enable);
				
		$form->addCommandButton("saveSettings", $lng->txt("save"));
		$form->addCommandButton("defaults", $lng->txt("cancel"));
		
		$tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}
	
	/**
	* Save survey settings
	*/
	function saveSettingsObject()
	{
		global $ilCtrl;

		$surveySetting = new ilSetting("survey");
		if ($_POST["unlimited_invitation"])
		{
			$surveySetting->set("unlimited_invitation", "1");
		}
		else
		{
			$surveySetting->set("unlimited_invitation", "0");
		}
		$ilCtrl->redirect($this, "settings");
	}
	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}

	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess;

		if ($ilAccess->checkAccess("read",'',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "settings"), array("settings","","view"), "", "");
		}
		if ($ilAccess->checkAccess("edit_permission",'',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
} // END class.ilObjSurveyAdministrationGUI
?>
