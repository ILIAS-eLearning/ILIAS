<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject2GUI.php");

/**
 * Wiki settings gui class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjWikiSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjWikiSettingsGUI: ilAdministrationGUI
 *
 * @ingroup ModulesWiki
 */
class ilObjWikiSettingsGUI extends ilObject2GUI
{
	/**
	 * Get type
	 */
	function getType()
	{
		return "wiks";
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem, $ilErr, $ilAccess, $lng;
		
		$lng->loadLanguageModule("wiki");

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if (!$ilAccess->checkAccess('read','',$this->object->getRefId()))
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

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editSettings";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	* Edit news settings.
	*/
	public function editSettings()
	{
		global $ilCtrl, $lng, $ilTabs, $ilToolbar, $tpl;
		
		$ilTabs->activateTab("settings");
		
		if ($this->checkPermissionBool("read"))
		{
			$form = $this->initForm();
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;

		$set = new ilSetting("wiki");
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// captcha
		/*$cb = new ilCheckboxInputGUI($this->lng->txt("wiki_act_captcha"), "captcha");
		$cb->setInfo($this->lng->txt("wiki_act_captcha_info"));
		$cb->setChecked($set->get("chaptcha"));
		$form->addItem($cb);*/
		
		if ($this->checkPermissionBool("write"))
		{
			$form->addCommandButton("saveSettings", $lng->txt("save"));
		}
	                
		$form->setTitle($lng->txt("settings"));
		$form->setFormAction($ilCtrl->getFormAction($this));
	 
		return $form;
	}
	
	/**
	 * Save settings
	 *
	 * @param
	 * @return
	 */
	function saveSettings()
	{
		global $ilCtrl, $lng;
		
		if ($this->checkPermissionBool("write"))
		{
			$set = new ilSetting("wiki");
//			$set->set("captcha", (int) $_POST["captcha"]);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		
		$ilCtrl->redirect($this, "editSettings");
	}
	
	
	/**
	 * administration tabs show only permissions and trash folder
	 */
	function getAdminTabs($tabs_gui)
	{
		global $tree;

		if ($this->checkPermissionBool("visible,read"))
		{
			$tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "editSettings"));

		}
		
		if ($this->checkPermissionBool("edit_permission"))
		{
			$tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
			);
		}
	}
}
?>