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

include_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjSurveyAdministrationGUI
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjSurveyAdministrationGUI: ilPermissionGUI, ilSettingsTemplateGUI
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
	}
	
	function &executeCommand()
	{
		global $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$ilTabs->activateTab("perm_settings");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilsettingstemplategui':
				$ilTabs->activateTab("templates");
				include_once("./Services/Administration/classes/class.ilSettingsTemplateGUI.php");
				$set_tpl_gui = new ilSettingsTemplateGUI($this->getSettingsTemplateConfig());
				$this->ctrl->forwardCommand($set_tpl_gui);
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
		// #7927: special users are deprecated
		exit();
		
		/*
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
		*/
	}
	
	function removeSpecialUserObject()
	{
		// #7927: special users are deprecated
		exit();
		
		/*
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
		*/
	}
	
	/**
	* Add/remove users who may run a survey multiple times
	*
	* @access	public
	*/
	function specialusersObject()
	{
		global $ilAccess, $ilTabs;
		
		// #7927: special users are deprecated
		exit();

		/*
		$ilTabs->activateTab("specialusers");
		
		$a_write_access = ($ilAccess->checkAccess("write", "", $this->object->getRefId())) ? true : false;
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_adm_specialusers.html", "Modules/Survey");
		$found_users = "";
		if (array_key_exists("survey_adm_found_users", $_SESSION))
		{
			if (count($_SESSION["survey_adm_found_users"]))
			{
				$data = $_SESSION["survey_adm_found_users"];
				include_once("./Modules/Survey/classes/tables/class.ilFoundUsersTableGUI.php");
				$table_gui = new ilFoundUsersTableGUI($this, "specialusers");
				$table_gui->setPrefix("fu");
				
				$table_gui->setTitle($this->lng->txt("found_users"));
				$table_gui->setData($data);
				
				if ($a_write_access)
				{
					$table_gui->addCommandButton("addSpecialUser", $this->lng->txt("add"));
					$table_gui->setSelectAllCheckbox("user_id");
				}
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
			include_once("./Modules/Survey/classes/tables/class.ilSpecialUsersTableGUI.php");
			$table_gui = new ilSpecialUsersTableGUI($this, "specialusers", $a_write_access);
			$table_gui->setPrefix("su");
					
			$table_gui->setTitle($this->lng->txt("adm_special_users"));
			$table_gui->setData($special_users);
			
			if ($a_write_access)
			{
				$table_gui->addCommandButton("removeSpecialUser", $this->lng->txt("remove"));
				$table_gui->setSelectAllCheckbox("special_user_id");
			}
			$this->tpl->setVariable("SPECIAL_USERS", $table_gui->getHTML());
		}
		else
		{
			$this->tpl->setVariable("SPECIAL_USERS", $this->lng->txt("adm_no_special_users"));
		}
		$this->tpl->parseCurrentBlock();		 
		*/
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
		global $ilAccess, $rbacreview, $lng, $ilCtrl, $tpl, $ilTabs;

		$ilTabs->activateTab("settings");
		
		$surveySetting = new ilSetting("survey");
		$unlimited_invitation = array_key_exists("unlimited_invitation", $_GET) ? $_GET["unlimited_invitation"] : $surveySetting->get("unlimited_invitation");	
		$use_anonymous_id = array_key_exists("use_anonymous_id", $_GET) ? $_GET["use_anonymous_id"] : $surveySetting->get("use_anonymous_id");
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("survey_defaults"));
		
		// unlimited invitation
		$enable = new ilCheckboxInputGUI($lng->txt("survey_unlimited_invitation"), "unlimited_invitation");
		$enable->setChecked($unlimited_invitation);
		$enable->setInfo($lng->txt("survey_unlimited_invitation_desc"));
		$form->addItem($enable);
		
		// Survey Code
		$code = new ilCheckboxInputGUI($lng->txt("use_anonymous_id"), "use_anonymous_id");
		$code->setChecked($use_anonymous_id);
		$code->setInfo($lng->txt("use_anonymous_id_desc"));
		$form->addItem($code);
		
		// Skipped
		$eval_skipped = new ilRadioGroupInputGUI($lng->txt("svy_eval_skipped_value"), "skcust");
		$eval_skipped->setRequired(true);
		$form->addItem($eval_skipped);
		
		$eval_skipped->setValue($surveySetting->get("skipped_is_custom", false) 
			? "cust"
			: "lng");
		
		$skipped_lng = new ilRadioOption($lng->txt("svy_eval_skipped_value_lng"), "lng");
		$skipped_lng->setInfo(sprintf($lng->txt("svy_eval_skipped_value_lng_info"), $lng->txt("skipped")));
		$eval_skipped->addOption($skipped_lng);
		$skipped_cust = new ilRadioOption($lng->txt("svy_eval_skipped_value_custom"), "cust");
		$skipped_cust->setInfo($lng->txt("svy_eval_skipped_value_custom_info"));
		$eval_skipped->addOption($skipped_cust);
		
		$skipped_cust_value = new ilTextInputGUI($lng->txt("svy_eval_skipped_value_custom_value"), "cust_value");
		$skipped_cust_value->setSize(15);
		$skipped_cust_value->setValue($surveySetting->get("skipped_custom_value", ""));
		$skipped_cust->addSubItem($skipped_cust_value);

		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$form->addCommandButton("saveSettings", $lng->txt("save"));
		}
		
		$tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}
	
	/**
	* Save survey settings
	*/
	function saveSettingsObject()
	{
		global $ilCtrl, $ilAccess;

		if (!$ilAccess->checkAccess("write", "", $this->object->getRefId())) $ilCtrl->redirect($this, "settings");
		$surveySetting = new ilSetting("survey");
		$surveySetting->set("unlimited_invitation", ($_POST["unlimited_invitation"]) ? "1" : "0");
		$surveySetting->set("use_anonymous_id", ($_POST["use_anonymous_id"]) ? "1" : "0");
		
		if($_POST["skcust"] == "lng")
		{
			$surveySetting->set("skipped_is_custom", false);
		}
		else
		{
			$surveySetting->set("skipped_is_custom", true);
			$surveySetting->set("skipped_custom_value", trim($_POST["cust_value"]));
		}
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
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
		global $ilAccess, $lng;

		if ($ilAccess->checkAccess("read",'',$this->object->getRefId()))
		{
			$tabs_gui->addTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "settings"));

			// #7927: special users are deprecated
			/*
			$tabs_gui->addTab("specialusers",
				$lng->txt("specialusers"),
				$this->ctrl->getLinkTarget($this, "specialusers"));			
			*/

			$tabs_gui->addTab("templates",
				$lng->txt("adm_settings_templates"),
				$this->ctrl->getLinkTargetByClass("ilsettingstemplategui", ""));
		}
		if ($ilAccess->checkAccess("edit_permission",'',$this->object->getRefId()))
		{
			$tabs_gui->addTab("perm_settings",
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"));
		}
	}

	/**
	 * Get settings template configuration object
	 *
	 * @return object settings template configuration object
	 */
	private function getSettingsTemplateConfig()
	{
		global $lng;

		$lng->loadLanguageModule("survey");

		include_once("./Services/Administration/classes/class.ilSettingsTemplateConfig.php");
		$config = new ilSettingsTemplateConfig("svy");

		$config->addHidableTab("survey_question_editor", $lng->txt("survey_question_editor_settings_template"));
		$config->addHidableTab("constraints", $lng->txt("constraints"));
		$config->addHidableTab("invitation", $lng->txt("invitation"));
		$config->addHidableTab("meta_data", $lng->txt("meta_data"));
		$config->addHidableTab("export", $lng->txt("export"));

		$config->addSetting(
			"use_pool",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("survey_question_pool_usage"),
			true
			);

		$config->addSetting(
			"anonymization_options",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("survey_auth_mode"),
			true,
			'personalized',
			array('personalized' => $this->lng->txt("anonymize_personalized"),
				'anonymize_without_code' => $this->lng->txt("anonymize_without_code"),
				'anonymize_with_code' => $this->lng->txt("anonymize_with_code"))
			);

		$config->addSetting(
			"rte_switch",
			ilSettingsTemplateConfig::SELECT,
			$lng->txt("set_edit_mode"),
			true,
			0,
			array(0 => $this->lng->txt("rte_editor_disabled"),
				1 => $this->lng->txt("rte_editor_enabled"))
			);
		
		$config->addSetting(
			"enabled_start_date",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("start_date"),
			true
			);

		$config->addSetting(
			"enabled_end_date",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("end_date"),
			true
			);

		$config->addSetting(
			"show_question_titles",
			ilSettingsTemplateConfig::BOOL,
			$lng->txt("svy_show_questiontitles"),
			true
			);

		return $config;
	}

} // END class.ilObjSurveyAdministrationGUI
?>