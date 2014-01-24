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
	 *
	 * @param
	 * @return
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
	 * @param ilPropertyFormGUI $form
	 */
	protected function editSettings(ilPropertyFormGUI $form = null)
	{
		global $ilCtrl, $lng, $ilTabs, $ilToolbar, $tpl;
		
		$ilTabs->activateTab("settings");
		
		if ($this->checkPermissionBool("read"))
		{
			if(!$form)
			{
				$form = $this->initForm();
				$this->populateWithCurrentSettings($form);
			}
			$tpl->setContent($form->getHTML());
		}
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	protected function populateWithCurrentSettings(ilPropertyFormGUI $form)
	{
		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';

		$form->setValuesByArray(array(
			'activate_captcha_anonym' => ilCaptchaUtil::isActiveForWiki()
		));
	}

	/**
	 * @param string $a_mode
	 * @return ilPropertyFormGUI
	 */
	public function initForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
		$cap = new ilCheckboxInputGUI($this->lng->txt('adm_captcha_anonymous_short'), 'activate_captcha_anonym');
		$cap->setInfo($this->lng->txt('adm_captcha_anonymous_wiki'));
		$cap->setValue(1);
		if(!ilCaptchaUtil::checkFreetype())
		{
			$cap->setAlert(ilCaptchaUtil::getPreconditionsMessage());
		}
		$form->addItem($cap);
		
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
	 */
	protected function saveSettings()
	{
		/**
		 * @var $lng ilLanguage
		 * @var $ilCtrl ilCtrl
		 */
		global $lng, $ilCtrl;

		if(!$this->checkPermissionBool("write"))
		{
			$this->editSettings();
			return;
		}

		$form = $this->initForm();
		if(!$form->checkInput())
		{
			$form->setValuesByPost();
			$this->editSettings($form);
			return;
		}

		require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
		ilCaptchaUtil::setActiveForWiki((bool)$form->getInput('activate_captcha_anonym'));

		ilUtil::sendSuccess($lng->txt('msg_obj_modified'), true);
		$ilCtrl->redirect($this, 'editSettings');
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

	/**
	 * @param string $a_form_id
	 * @return array
	 */
	public function addToExternalSettingsForm($a_form_id)
	{
		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_ACCESSIBILITY:
				require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
				$fields = array(
					'adm_captcha_anonymous_short' => array(ilCaptchaUtil::isActiveForWiki(), ilAdministrationSettingsFormHandler::VALUE_BOOL)
				);

				return array('obj_wiks' => array('editSettings', $fields));
		}
	}
}
?>