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
* @ingroup ModulesSurvey
* 
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
		global $rbacsystem, $lng;

		$this->type = "svyf";
		$lng->loadLanguageModule("survey");
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
		ilUtil::sendSuccess($this->lng->txt("object_added"),true);

		$this->ctrl->redirect($this);
	}

	function searchObject()
	{
		unset($_SESSION["survey_adm_found_users"]);
		if (strlen($_POST["search"]) < 2)
		{
			ilUtil::sendInfo($this->lng->txt("adm_search_string_too_small"), TRUE);
		}
		else
		{
			include_once "./Services/User/classes/class.ilObjUser.php";
			$found = ilObjUser::searchUsers($_POST["search"], $active = 1, $a_return_ids_only = false, $filter_settings = FALSE);
			if (count($found))
			{
				$_SESSION["survey_adm_found_users"] = $found;
			}
			else
			{
				ilUtil::sendInfo($this->lng->txt("adm_no_users_found"), TRUE);
				
			}
		}
		$this->ctrl->redirect($this, "specialusers");
	}
	
	/**
	* Add one or more users as special users
	* 
	*/
	function addSpecialUserObject()
	{
		if ((array_key_exists("user_id", $_POST)) && (count($_POST["user_id"])))
		{
			$this->object->addSpecialUsers($_POST["user_id"]);
			unset($_SESSION["survey_adm_found_users"]);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("adm_search_select_user"), TRUE);
		}
		$this->ctrl->redirect($this, "specialusers");
	}
	
	function removeSpecialUserObject()
	{
		if ((array_key_exists("special_user_id", $_POST)) && (count($_POST["special_user_id"])))
		{
			$this->object->removeSpecialUsers($_POST["special_user_id"]);
			unset($_SESSION["adm_removed_users"]);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("adm_remove_select_user"), TRUE);
		}
		$this->ctrl->redirect($this, "specialusers");
			}
	
	/**
	* Add/remove users who may run a survey multiple times
	*
	* @access	public
	*/
	function specialusersObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_adm_specialusers.html", "Modules/Survey");
		$found_users = "";
		if (array_key_exists("survey_adm_found_users", $_SESSION))
		{
			if (count($_SESSION["survey_adm_found_users"]))
			{
				$data = $_SESSION["survey_adm_found_users"];
				include_once("./Modules/Survey/classes/class.ilFoundUsersTableGUI.php");
				$table_gui = new ilFoundUsersTableGUI($this, "specialusers");
				$table_gui->setPrefix("fu");
				
				$table_gui->setTitle($this->lng->txt("found_users"));
				$table_gui->setData($data);
				
				$table_gui->addCommandButton("addSpecialUser", $this->lng->txt("add"));
				$table_gui->setSelectAllCheckbox("user_id");
				$found_users = $table_gui->getHTML();
			}
		}
		
		if (strlen($found_users))
		{
			$this->tpl->setCurrentBlock("search_results");
			$this->tpl->setVariable("SEARCH_RESULTS", $found_users);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TXT_SEARCH_USER", $this->lng->txt("search_users"));
		$this->tpl->setVariable("TXT_SEARCH", $this->lng->txt("search"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "search"));

		$special_users = $this->object->getSpecialUsers();
		if (count($special_users))
		{
			include_once("./Modules/Survey/classes/class.ilSpecialUsersTableGUI.php");
			$table_gui = new ilSpecialUsersTableGUI($this, "specialusers");
			$table_gui->setPrefix("su");
					
			$table_gui->setTitle($this->lng->txt("adm_special_users"));
			$table_gui->setData($special_users);
			
			$table_gui->addCommandButton("removeSpecialUser", $this->lng->txt("remove"));
			$table_gui->setSelectAllCheckbox("special_user_id");
			$this->tpl->setVariable("SPECIAL_USERS", $table_gui->getHTML());
		}
		else
		{
			$this->tpl->setVariable("SPECIAL_USERS", $this->lng->txt("adm_no_special_users"));
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	* display survey settings form
	* 
	* Default settings tab for Survey settings
	*
	* @access	public
	*/
	function settingsObject()
	{
		global $ilAccess, $rbacreview, $lng, $ilCtrl, $tpl;
		
		$surveySetting = new ilSetting("survey");
		$unlimited_invitation = array_key_exists("unlimited_invitation", $_GET) ? $_GET["unlimited_invitation"] : $surveySetting->get("unlimited_invitation");
		$googlechart = array_key_exists("googlechart", $_GET) ? $_GET["googlechart"] : $surveySetting->get("googlechart");
		$use_anonymous_id = array_key_exists("use_anonymous_id", $_GET) ? $_GET["use_anonymous_id"] : $surveySetting->get("use_anonymous_id");
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
				
		// Google chart API
		$enable = new ilCheckboxInputGUI($lng->txt("use_google_chart_api"), "googlechart");
		$enable->setChecked($googlechart);
		$enable->setInfo($lng->txt("use_google_chart_api_desc"));
		$form->addItem($enable);

		// Survey Code
		$code = new ilCheckboxInputGUI($lng->txt("use_anonymous_id"), "use_anonymous_id");
		$code->setChecked($use_anonymous_id);
		$code->setInfo($lng->txt("use_anonymous_id_desc"));
		$form->addItem($code);

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
		$surveySetting->set("unlimited_invitation", ($_POST["unlimited_invitation"]) ? "1" : "0");
		$surveySetting->set("googlechart", ($_POST["googlechart"]) ? "1" : "0");
		$surveySetting->set("use_anonymous_id", ($_POST["use_anonymous_id"]) ? "1" : "0");
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
			$tabs_gui->addTarget("settings", $this->ctrl->getLinkTarget($this, "settings"), array("settings","","view"), "", "");
			$tabs_gui->addTarget("specialusers", $this->ctrl->getLinkTarget($this, "specialusers"), array("specialusers"), "", "");
		}
		if ($ilAccess->checkAccess("edit_permission",'',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
} // END class.ilObjSurveyAdministrationGUI
?>
