<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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


/**
 * Class ilObjLanguageFolderGUI
 *
 * @author	Stefan Meyer <smeyer@databay.de>
 * @version	$Id$
 * 
 * @ilCtrl_Calls ilObjLanguageFolderGUI: ilPermissionGUI
 *
 * @extends	ilObject

 */

require_once "./Services/Language/classes/class.ilObjLanguage.php";
require_once "./classes/class.ilObjectGUI.php";

class ilObjLanguageFolderGUI extends ilObjectGUI
{
	/**
	 * Constructor
	 * 
	 * @access public
	 */
	function ilObjLanguageFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "lngf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
		$_GET["sort_by"] = "language";
	}

	/**
	 * show installed languages
	 *
	 * @access	public
	 */
	function viewObject()
	{
		global $rbacsystem, $ilSetting;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		if ($ilSetting->get("lang_ext_maintenance") == "1")
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",
				$this->ctrl->getLinkTarget($this, "confirmRefresh"));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("refresh_languages"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",
				$this->ctrl->getLinkTarget($this, "refresh"));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("refresh_languages"));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",
			$this->ctrl->getLinkTarget($this, "checkLanguage"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("check_languages"));
		$this->tpl->parseCurrentBlock();
		
		// extended language maintenance
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			if ($ilSetting->get("lang_ext_maintenance") == "1")
			{
				$this->tpl->setCurrentBlock("btn_cell");
				$this->tpl->setVariable("BTN_LINK",
					$this->ctrl->getLinkTarget($this, "disableExtendedLanguageMaintenance"));
				$this->tpl->setVariable("BTN_TXT",$this->lng->txt("disable_ext_lang_maint"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("btn_cell");
				$this->tpl->setVariable("BTN_LINK",
					$this->ctrl->getLinkTarget($this, "enableExtendedLanguageMaintenance"));
				$this->tpl->setVariable("BTN_TXT",$this->lng->txt("enable_ext_lang_maint"));
				$this->tpl->parseCurrentBlock();
			}
		}

		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("","language","status","last_change", "usr_agreement");

		$languages = $this->object->getLanguages();
		
		foreach ($languages as $lang_key => $lang_data)
		{
			$status = "";
	
			// set status info (in use or systemlanguage)
			if ($lang_data["status"])
			{
				$status = "<span class=\"small\"> (".$this->lng->txt($lang_data["status"]).")</span>";
			}

			// set remark color
			switch ($lang_data["info"])
			{
				case "file_not_found":
					$remark = "<span class=\"smallred\"> ".$this->lng->txt($lang_data["info"])."</span>";
					break;
				case "new_language":
					$remark = "<span class=\"smallgreen\"> ".$this->lng->txt($lang_data["info"])."</span>";
					break;
				default:
					$remark = "";
					break;
			}

			if (file_exists("./Customizing/clients/".CLIENT_ID."/agreement/".
				"agreement_".$lang_key.".html"))
			{
				$agreement_exists_str = $this->lng->txt("available")." (".$this->lng->txt("client").")";
			}
			else if (file_exists("./Customizing/global/agreement/".
				"agreement_".$lang_key.".html"))
			{
				$agreement_exists_str = $this->lng->txt("available");
			}
			else
			{
				if ($lang_data["status"] == "system_language")
				{
					$agreement_exists_str = "<b>".$this->lng->txt("missing")."</b>";
				}
				else
				{
					$agreement_exists_str = $this->lng->txt("missing");
				}
			}

			// make language name clickable
			if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
			{
				if ($ilSetting->get("lang_ext_maintenance") == "1")
				{
					if (substr($lang_data["description"],0,9) == "installed")
					{
						$this->ctrl->setParameterByClass("ilobjlanguageextgui","obj_id",$lang_data["obj_id"]);
						$url = $this->ctrl->getLinkTargetByClass("ilobjlanguageextgui","");
						$lang_data["name"] = '<a href="'.$url.'">'.$lang_data["name"].'</a>';
					}
				}
			}

			// visible data part
			$this->data["data"][] = array(
									"language"		=> $lang_data["name"].$status,
									"status"		=> $this->lng->txt($lang_data["desc"])."<br/>".$remark,
									"last_change"	=> $lang_data["last_update"],
									"obj_id"		=> $lang_data["obj_id"],
									"agreement"		=> $agreement_exists_str
										);

		}
	
		$this->maxcount = count($this->data["data"]);

		// sorting array
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
											"obj_id"	=> $val["obj_id"],
											"type"		=> $val["type"]
											);		

			unset($this->data["data"][$key]["obj_id"]);
			$this->data["data"][$key]["last_change"] = ilDatePresentation::formatDate(new ilDateTime($this->data["data"][$key]["last_change"],IL_CAL_DATETIME));
			
		}

		$this->displayList();
	}

	/**
	 * display object list
	 *
	 * @access	public
 	 */
	function displayList()
	{
		global $tree, $rbacsystem;

		require_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));

		// create table
		$tbl = new ilTableGUI();
		
		// title & header columns
		//$tbl->setTitle($this->lng->txt("obj_".$this->object->getType()),"icon_".$this->object->getType().".gif",$this->lng->txt("obj_".$this->object->getType()));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		//$tbl->disable("header");
		$tbl->disable("title");
		
		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}
		
		$tbl->setHeaderNames($header_names);

		$header_params = array("ref_id" => $this->ref_id);
		$tbl->setHeaderVars($this->data["cols"],$header_params);
		
		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit(0);
		$tbl->setOffset(0);
		$tbl->setMaxCount($this->maxcount);
		$tbl->disable("sort");
		
		// SHOW VALID ACTIONS
		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));
		$this->showActions();
		
		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");
		
		// render table
		$tbl->render();

		if (is_array($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				$this->tpl->setCurrentBlock("checkbox");
				$this->tpl->setVariable("CHECKBOX_ID",$ctrl["obj_id"]);
				$this->tpl->setVariable("CSS_ROW",$css_row);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{

					$this->tpl->setCurrentBlock("text");

					$this->tpl->setVariable("TEXT_CONTENT", $val);					
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		} //if is_array
	}

	/**
	 * install languages
	 */
	function installObject()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["id"] as $obj_id)
		{
			$langObj = new ilObjLanguage($obj_id);
			$key = $langObj->install();

			if ($key != "")
			{
				$lang_installed[] = $key;
			}

			unset($langObj);
		}

		if (isset($lang_installed))
		{
			if (count($lang_installed) == 1)
			{
				$this->data = $this->lng->txt("lang_" . $lang_installed[0]) . " " . strtolower($this->lng->txt("installed")) . ".";
			}
			else
			{
				foreach ($lang_installed as $lang_key)
				{
					$langnames[] = $this->lng->txt("lang_" . $lang_key);
				}
				$this->data = implode(", ",$langnames) . " " . strtolower($this->lng->txt("installed")) . ".";
			}
		}
		else
		{
			$this->data = $this->lng->txt("languages_already_installed");
		}

		$this->out();
	}


	/**
	 * Install local language modifications.
	 */
	function installLocalObject()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["id"] as $obj_id)
		{
			$langObj = new ilObjLanguage($obj_id);
			$key = $langObj->install();

			if ($key != "")
			{
				$lang_installed[] = $key;
			}

			unset($langObj);

			$langObj = new ilObjLanguage($obj_id);
			$key = $langObj->install('local');

			if ($key != "")
			{
				$local_installed[] = $key;
			}

			unset($langObj);
		}

		if (isset($lang_installed))
		{
			if (count($lang_installed) == 1)
			{
				$this->data = $this->lng->txt("lang_" . $lang_installed[0]) . " " . strtolower($this->lng->txt("installed")) . ".";
			}
			else
			{
				foreach ($lang_installed as $lang_key)
				{
					$langnames[] = $this->lng->txt("lang_" . $lang_key);
				}
				$this->data = implode(", ",$langnames) . " " . strtolower($this->lng->txt("installed")) . ".";
			}
		}

		if (isset($local_installed))
		{
			if (count($local_installed) == 1)
			{
				$this->data .= " " . $this->lng->txt("lang_" . $local_installed[0]) . " " . $this->lng->txt("local_language_file") . " " . strtolower($this->lng->txt("installed")) . ".";
			}
			else
			{
				foreach ($local_installed as $lang_key)
				{
					$langnames[] = $this->lng->txt("lang_" . $lang_key);
				}
				$this->data .= " " . implode(", ",$langnames) . " " . $this->lng->txt("local_language_files") . " " . strtolower($this->lng->txt("installed")) . ".";
			}
		}
		else
		{
			$this->data .= " " . $this->lng->txt("local_languages_already_installed");
		}

		$this->out();
	}


	/**
	 * uninstall language
	 */
	function uninstallObject()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// uninstall all selected languages
		foreach ($_POST["id"] as $obj_id)
		{
			$langObj = new ilObjLanguage($obj_id);
			if (!($sys_lang = $langObj->isSystemLanguage()))
				if (!($usr_lang = $langObj->isUserLanguage()))
				{
					$key = $langObj->uninstall();
					if ($key != "")
						$lang_uninstalled[] = $key;
				}
			unset($langObj);
		}

		// generate output message
		if (isset($lang_uninstalled))
		{
			if (count($lang_uninstalled) == 1)
			{
				$this->data = $this->lng->txt("lang_".$lang_uninstalled[0])." ".$this->lng->txt("uninstalled");
			}
			else
			{
				foreach ($lang_uninstalled as $lang_key)
				{
					$langnames[] = $this->lng->txt("lang_".$lang_key);
				}

				$this->data = implode(", ",$langnames)." ".$this->lng->txt("uninstalled");
			}
		}
		elseif ($sys_lang)
		{
			$this->data = $this->lng->txt("cannot_uninstall_systemlanguage");
		}
		elseif ($usr_lang)
		{
			$this->data = $this->lng->txt("cannot_uninstall_language_in_use");
		}
		else
		{
			$this->data = $this->lng->txt("languages_already_uninstalled");
		}

		$this->out();
	}

	/**
	 * update all installed languages
	 */
	function refreshObject()
	{
		$languages = ilObject::_getObjectsByType("lng");

		foreach ($languages as $lang)
		{
			$langObj = new ilObjLanguage($lang["obj_id"],false);

			if ($langObj->isInstalled() == true)
			{
				if ($langObj->check())
				{
					$langObj->flush('keep_local');
					$langObj->insert();
					$langObj->setTitle($langObj->getKey());
					$langObj->setDescription($langObj->getStatus());
					$langObj->update();
					$langObj->optimizeData();

					if ($langObj->isLocal() == true)
					{
						if ($langObj->check('local'))
						{
							$langObj->insert('local');
							$langObj->setTitle($langObj->getKey());
							$langObj->setDescription($langObj->getStatus());
							$langObj->update();
							$langObj->optimizeData();
						}
					}
				}
			}

			unset($langObj);
		}

		$this->data = $this->lng->txt("languages_updated");

		$this->out();
	}


	/**
	 * update selected languages
	 */
	function refreshSelectedObject()
	{
		global $lng;
		
		$this->data = $this->lng->txt("selected_languages_updated");
		$lng->loadLanguageModule("meta");

		foreach ($_POST["id"] as $id)
		{
			$langObj = new ilObjLanguage($id, false);

			if ($langObj->isInstalled() == true)
			{
				if ($langObj->check())
				{
					$langObj->flush('keep_local');
					$langObj->insert();
					$langObj->setTitle($langObj->getKey());
					$langObj->setDescription($langObj->getStatus());
					$langObj->update();
					$langObj->optimizeData();

					if ($langObj->isLocal() == true)
					{
						if ($langObj->check('local'))
						{
							$langObj->insert('local');
							$langObj->setTitle($langObj->getKey());
							$langObj->setDescription($langObj->getStatus());
							$langObj->update();
							$langObj->optimizeData();
						}
					}
				}
				$this->data .= "<br />". $lng->txt("meta_l_".$langObj->getKey());
			}

			unset($langObj);
		}

		$this->out();
	}


	/**
	 * set user language
	 */
	function setUserLanguageObject()
	{
		require_once './Services/User/classes/class.ilObjUser.php';

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["id"]) != 1)
		{
			$this->ilias->raiseError($this->lng->txt("choose_only_one_language")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		$obj_id = $_POST["id"][0];

		$newUserLangObj = new ilObjLanguage($obj_id);

		if ($newUserLangObj->isUserLanguage())
		{
			$this->ilias->raiseError($this->lng->txt("lang_".$newUserLangObj->getKey())." ".$this->lng->txt("is_already_your")." ".$this->lng->txt("user_language")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		if ($newUserLangObj->isInstalled() == false)
		{
			$this->ilias->raiseError($this->lng->txt("lang_".$newUserLangObj->getKey())." ".$this->lng->txt("language_not_installed")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		$curUser = new ilObjUser($_SESSION["AccountId"]);
		$curUser->setLanguage($newUserLangObj->getKey());
		$curUser->update();
		//$this->setUserLanguage($new_lang_key);

		$this->data = $this->lng->txt("user_language")." ".$this->lng->txt("changed_to")." ".$this->lng->txt("lang_".$newUserLangObj->getKey()).".";

		$this->out();
	}


	/**
	 * set the system language
	 */
	function setSystemLanguageObject()
	{
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		if (count($_POST["id"]) != 1)
		{
			$this->ilias->raiseError($this->lng->txt("choose_only_one_language")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		$obj_id = $_POST["id"][0];

		$newSysLangObj = new ilObjLanguage($obj_id);

		if ($newSysLangObj->isSystemLanguage())
		{
			$this->ilias->raiseError($this->lng->txt("lang_".$newSysLangObj->getKey())." is already the system language!<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		if ($newSysLangObj->isInstalled() == false)
		{
			$this->ilias->raiseError($this->lng->txt("lang_".$newSysLangObj->getKey())." is not installed. Please install that language first.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		$this->ilias->setSetting("language", $newSysLangObj->getKey());

		// update ini-file
		$this->ilias->ini->setVariable("language","default",$newSysLangObj->getKey());
		$this->ilias->ini->write();

		$this->data = $this->lng->txt("system_language")." ".$this->lng->txt("changed_to")." ".$this->lng->txt("lang_".$newSysLangObj->getKey()).".";

		$this->out();
	}


	/**
	 * check all languages
	 */
	function checkLanguageObject()
	{
		//$langFoldObj = new ilObjLanguageFolder($_GET["obj_id"]);
		//$this->data = $langFoldObj->checkAllLanguages();
		$this->data = $this->object->checkAllLanguages();
		$this->out();
	}


	function out()
	{
		ilUtil::sendInfo($this->data,true);
		$this->ctrl->redirect($this, "view");
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
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "view"), array("view",""), "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
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
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
	/**
	* Enable extended language maintenance.
	*/
	function enableExtendedLanguageMaintenanceObject()
	{
		global $ilSetting, $ilCtrl;
		
		$ilSetting->set("lang_ext_maintenance", 1);
		$ilCtrl->redirect($this, "view");
	}
	
	/**
	* Disable extended language maintenance.
	*/
	function disableExtendedLanguageMaintenanceObject()
	{
		global $ilSetting, $ilCtrl;
		
		$ilSetting->set("lang_ext_maintenance", 0);
		$ilCtrl->redirect($this, "view");
	}
	
	function confirmRefreshObject()
	{
		global $ilCtrl, $lng;
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$conf_screen = new ilConfirmationGUI();
		$conf_screen->setFormAction($ilCtrl->getFormAction($this));
		$conf_screen->setHeaderText($lng->txt("lang_refresh_confirm"));
		$conf_screen->addItem("d", "d", $lng->txt("lang_refresh_confirm_info"));
		$conf_screen->setCancel($lng->txt("cancel"), "view");
		$conf_screen->setConfirm($lng->txt("ok"), "refresh");
		
		$this->tpl->setContent($conf_screen->getHTML());
	}

	function confirmRefreshSelectedObject()
	{
		global $ilCtrl, $lng;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		$lng->loadLanguageModule("meta");
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$conf_screen = new ilConfirmationGUI();
		$conf_screen->setFormAction($ilCtrl->getFormAction($this));
		$conf_screen->setHeaderText($lng->txt("lang_refresh_confirm_selected"));
		foreach ($_POST["id"] as $id)
		{
			$lang_title = ilObject::_lookupTitle($id);
			$conf_screen->addItem("id[]", $id, $lng->txt("meta_l_".$lang_title));
		}
		$conf_screen->addItem("d", "d", $lng->txt("lang_refresh_confirm_info"));
		$conf_screen->setCancel($lng->txt("cancel"), "view");
		$conf_screen->setConfirm($lng->txt("ok"), "refreshSelected");
		$this->tpl->setContent($conf_screen->getHTML());
	}

	/**
	* Get Actions
	*/
	function getActions()
	{
		global $ilSetting;
		
		// standard actions for container
		return array(
			"install" => array("name" => "install", "lng" => "install"),
			"installLocal" => array("name" => "installLocal", "lng" => "install_local"),
			"uninstall" => array("name" => "uninstall", "lng" => "uninstall"),
			"refresh" => $ilSetting->get("lang_ext_maintenance") == "1" ?
					array("name" => "confirmRefreshSelected", "lng" => "refresh") :
					array("name" => "RefreshSelected", "lng" => "refresh"),
			"setSystemLanguage" => array("name" => "setSystemLanguage", "lng" => "setSystemLanguage"),
			"setUserLanguage" => array("name" => "setUserLanguage", "lng" => "setUserLanguage")
		);
	}

	
} // END class.ilObjLanguageFolderGUI
?>
