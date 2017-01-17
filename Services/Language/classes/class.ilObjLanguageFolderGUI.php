<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLanguageFolderGUI
 *
 * @author	Stefan Meyer <meyer@leifos.com>
 * @version	$Id$
 * 
 * @ilCtrl_Calls ilObjLanguageFolderGUI: ilPermissionGUI
 *
 * @extends	ilObject

 */

require_once "./Services/Language/classes/class.ilObjLanguage.php";
require_once "./Services/Object/classes/class.ilObjectGUI.php";

class ilObjLanguageFolderGUI extends ilObjectGUI
{
	/**
	 * Constructor
	 * 
	 * @access public
	 */
	function __construct($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "lngf";
		parent::__construct($a_data,$a_id,$a_call_by_reference, false);
		$_GET["sort_by"] = "language";
		$this->lng->loadLanguageModule('lng');
	}

	/**
	 * show installed languages
	 *
	 * @access	public
	 */
	function viewObject()
	{
		global $rbacsystem, $ilSetting, $tpl, $ilToolbar, $lng, $ilClientIniFile;
		
		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// refresh
		$ilToolbar->addButton($lng->txt("refresh_languages"),
			$this->ctrl->getLinkTarget($this, "confirmRefresh"));

		// check languages
		$ilToolbar->addButton($lng->txt("check_languages"),
			$this->ctrl->getLinkTarget($this, "checkLanguage"));
		
		// extended language maintenance
		if ($rbacsystem->checkAccess("write",$this->object->getRefId()))
		{
			if(!$ilSetting->get('lang_detection'))
			{
				$ilToolbar->addButton($lng->txt('lng_enable_language_detection'), $this->ctrl->getLinkTarget($this, 'enableLanguageDetection'));
			}
			else
			{
				$ilToolbar->addButton($lng->txt('lng_disable_language_detection'),	$this->ctrl->getLinkTarget($this, 'disableLanguageDetection'));
			}
		}

		if($ilClientIniFile->variableExists('system', 'LANGUAGE_LOG'))
		{
			$ilToolbar->addButton($lng->txt('lng_download_deprecated'),	$this->ctrl->getLinkTarget($this, 'listDeprecated'));
		}

		include_once("./Services/Language/classes/class.ilLanguageTableGUI.php");
		$ltab = new ilLanguageTableGUI($this, "view", $this->object);
		$tpl->setContent($ltab->getHTML());
	}

	/**
	 * install languages
	 */
	function installObject()
	{
		$this->lng->loadLanguageModule("meta");

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
				$this->data = $this->lng->txt("meta_l_" . $lang_installed[0]) . " " . strtolower($this->lng->txt("installed")) . ".";
			}
			else
			{
				foreach ($lang_installed as $lang_key)
				{
					$langnames[] = $this->lng->txt("meta_l_" . $lang_key);
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
		$this->lng->loadLanguageModule("meta");

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
				$this->data = $this->lng->txt("meta_l_" . $lang_installed[0]) . " " . strtolower($this->lng->txt("installed")) . ".";
			}
			else
			{
				foreach ($lang_installed as $lang_key)
				{
					$langnames[] = $this->lng->txt("meta_l_" . $lang_key);
				}
				$this->data = implode(", ",$langnames) . " " . strtolower($this->lng->txt("installed")) . ".";
			}
		}

		if (isset($local_installed))
		{
			if (count($local_installed) == 1)
			{
				$this->data .= " " . $this->lng->txt("meta_l_" . $local_installed[0]) . " " . $this->lng->txt("local_language_file") . " " . strtolower($this->lng->txt("installed")) . ".";
			}
			else
			{
				foreach ($local_installed as $lang_key)
				{
					$langnames[] = $this->lng->txt("meta_l_" . $lang_key);
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
		$this->lng->loadLanguageModule("meta");

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
				$this->data = $this->lng->txt("meta_l_".$lang_uninstalled[0])." ".$this->lng->txt("uninstalled");
			}
			else
			{
				foreach ($lang_uninstalled as $lang_key)
				{
					$langnames[] = $this->lng->txt("meta_l_".$lang_key);
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
	 * Uninstall local changes in the database
	 */
	function uninstallChangesObject()
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
					$langObj->flush('all');
					$langObj->insert();
					$langObj->setTitle($langObj->getKey());
					$langObj->setDescription('installed');
					$langObj->update();
					$langObj->optimizeData();
				}
				$this->data .= "<br />". $lng->txt("meta_l_".$langObj->getKey());
			}

			unset($langObj);
		}

		$this->out();
	}


	/**
	 * update all installed languages
	 */
	function refreshObject()
	{
		ilObjLanguage::refreshAll();
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

		$refreshed = array();
		foreach ($_POST["id"] as $id)
		{
			$langObj = new ilObjLanguage($id, false);
			if ($langObj->refresh())
			{
				$refreshed[] = $langObj->getKey();
				$this->data .= "<br />". $lng->txt("meta_l_".$langObj->getKey());
			}
			unset($langObj);
		}

		ilObjLanguage::refreshPlugins($refreshed);
		$this->out();
	}


	/**
	 * set user language
	 */
	function setUserLanguageObject()
	{
		$this->lng->loadLanguageModule("meta");

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
			$this->ilias->raiseError($this->lng->txt("meta_l_".$newUserLangObj->getKey())." ".$this->lng->txt("is_already_your")." ".$this->lng->txt("user_language")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		if ($newUserLangObj->isInstalled() == false)
		{
			$this->ilias->raiseError($this->lng->txt("meta_l_".$newUserLangObj->getKey())." ".$this->lng->txt("language_not_installed")."<br/>".$this->lng->txt("action_aborted"),$this->ilias->error_obj->MESSAGE);
		}

		$curUser = new ilObjUser($GLOBALS['DIC']['ilUser']->getId());
		$curUser->setLanguage($newUserLangObj->getKey());
		$curUser->update();
		//$this->setUserLanguage($new_lang_key);

		$this->data = $this->lng->txt("user_language")." ".$this->lng->txt("changed_to")." ".$this->lng->txt("meta_l_".$newUserLangObj->getKey()).".";

		$this->out();
	}


	/**
	 * set the system language
	 */
	function setSystemLanguageObject()
	{
		$this->lng->loadLanguageModule("meta");

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
			$this->ilias->raiseError($this->lng->txt("meta_l_".$newSysLangObj->getKey())." is already the system language!<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		if ($newSysLangObj->isInstalled() == false)
		{
			$this->ilias->raiseError($this->lng->txt("meta_l_".$newSysLangObj->getKey())." is not installed. Please install that language first.<br>Action aborted!",$this->ilias->error_obj->MESSAGE);
		}

		$this->ilias->setSetting("language", $newSysLangObj->getKey());

		// update ini-file
		$this->ilias->ini->setVariable("language","default",$newSysLangObj->getKey());
		$this->ilias->ini->write();

		$this->data = $this->lng->txt("system_language")." ".$this->lng->txt("changed_to")." ".$this->lng->txt("meta_l_".$newSysLangObj->getKey()).".";

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
	
	function getAdminTabs()
	{
		$this->getTabs();
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "view"), array("view",""), "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
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

	function confirmRefreshObject()
	{
		$languages = ilObject::_getObjectsByType("lng");

		$ids = array();
		foreach ($languages as $lang)
		{
			$langObj = new ilObjLanguage($lang["obj_id"],false);
			if ($langObj->isInstalled() == true)
			{
				$ids[] = $lang["obj_id"];
			}
		}
		$this->confirmRefreshSelectedObject($ids);
	}

	function confirmRefreshSelectedObject($a_ids = array())
	{
		global $ilCtrl, $lng;

		if (!empty($a_ids))
		{
			$ids = $a_ids;
			$header = $lng->txt("lang_refresh_confirm");
		}
		elseif (!empty($_POST["id"]))
		{
			$ids = $_POST["id"];
			$header = $lng->txt("lang_refresh_confirm_selected");
		}
		else
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}
		
		$lng->loadLanguageModule("meta");
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

		$conf_screen = new ilConfirmationGUI();
		$some_changed = false;
		foreach ($ids as $id)
		{
			$lang_key = ilObject::_lookupTitle($id);
			$lang_title = $lng->txt('meta_l_'.$lang_key);
			$last_change = ilObjLanguage::_getLastLocalChange($lang_key);
			if (!empty($last_change))
			{
				$some_changed = true;
				$lang_title .= ' ('. $this->lng->txt("last_change"). ' '
					. ilDatePresentation::formatDate(new ilDateTime($last_change,IL_CAL_DATETIME)) . ')';
			}
			$conf_screen->addItem("id[]", $id, $lang_title);
		}

		$conf_screen->setFormAction($ilCtrl->getFormAction($this));
		if ($some_changed)
		{
			$header .= '<br />' . $lng->txt("lang_refresh_confirm_info");
		}
		$conf_screen->setHeaderText($header);
		$conf_screen->setCancel($lng->txt("cancel"), "view");
		$conf_screen->setConfirm($lng->txt("ok"), "refreshSelected");
		$this->tpl->setContent($conf_screen->getHTML());
	}

	function confirmUninstallObject()
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
		$conf_screen->setHeaderText($lng->txt("lang_uninstall_confirm"));
		foreach ($_POST["id"] as $id)
		{
			$lang_title = ilObject::_lookupTitle($id);
			$conf_screen->addItem("id[]", $id, $lng->txt("meta_l_".$lang_title));
		}
		$conf_screen->setCancel($lng->txt("cancel"), "view");
		$conf_screen->setConfirm($lng->txt("ok"), "uninstall");
		$this->tpl->setContent($conf_screen->getHTML());
	}


	function confirmUninstallChangesObject()
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
		$conf_screen->setHeaderText($lng->txt("lang_uninstall_changes_confirm"));
		foreach ($_POST["id"] as $id)
		{
			$lang_title = ilObject::_lookupTitle($id);
			$conf_screen->addItem("id[]", $id, $lng->txt("meta_l_".$lang_title));
		}
		$conf_screen->setCancel($lng->txt("cancel"), "view");
		$conf_screen->setConfirm($lng->txt("ok"), "uninstallChanges");
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
			"refresh" => array("name" => "confirmRefreshSelected", "lng" => "refresh"),
			"setSystemLanguage" => array("name" => "setSystemLanguage", "lng" => "setSystemLanguage"),
			"setUserLanguage" => array("name" => "setUserLanguage", "lng" => "setUserLanguage")
		);
	}

	/**
	 *
	 */
	protected function disableLanguageDetectionObject()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		$ilSetting->set('lang_detection', 0);
		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->viewObject();
	}

	/**
	 *
	 */
	protected function enableLanguageDetectionObject()
	{
		/**
		 * @var $ilSetting ilSetting
		 */
		global $ilSetting;

		$ilSetting->set('lang_detection', 1);
		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->viewObject();
	}

	/**
	 * Download deprecated lang entries
	 */
	function listDeprecatedObject()
	{
		global $DIC;

		$rbacsystem = $DIC->rbac()->system();
		$tpl = $DIC["tpl"];
		$ilToolbar = $DIC->toolbar();
		$ctrl = $DIC->ctrl();
		$lng = $DIC->language();

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		$ilToolbar->addButton($lng->txt("download"),
			$ctrl->getLinkTarget($this, "downloadDeprecated"));

		include_once("./Services/Language/classes/class.ilLangDeprecated.php");

		$d = new ilLangDeprecated();
		$res = "";
		foreach ($d->getDeprecatedLangVars() as $key => $mod)
		{
			$res.= $mod.",".$key."\n";
		}

		$tpl->setContent("<pre>".$res."</pre>");
	}

	/**
	 * Download deprecated lang entries
	 */
	function downloadDeprecatedObject()
	{
		global $DIC;

		$rbacsystem = $DIC->rbac()->system();

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/Language/classes/class.ilLangDeprecated.php");
		$d = new ilLangDeprecated();
		$res = "";
		foreach ($d->getDeprecatedLangVars() as $key => $mod)
		{
			$res.= $mod.",".$key."\n";
		}

		ilUtil::deliverData($res, "lang_deprecated.csv");
	}


} // END class.ilObjLanguageFolderGUI
?>
