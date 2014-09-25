<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for personal profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilPersonalSettingsGUI:
 */
class ilPersonalSettingsGUI
{
    var $tpl;
    var $lng;
    var $ilias;
	var $ctrl;

	/**
	 * constructor
	 */
    function __construct()
    {
        global $ilias, $tpl, $lng, $rbacsystem, $ilCtrl;

		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$this->user_defined_fields =& ilUserDefinedFields::_getInstance();

        $this->tpl =& $tpl;
        $this->lng =& $lng;
        $this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->settings = $ilias->getAllSettings();
//		$lng->loadLanguageModule("jsmath");
		$lng->loadLanguageModule('chatroom');
		$lng->loadLanguageModule('chatroom_adm');
		$this->upload_error = "";
		$this->password_error = "";
		$lng->loadLanguageModule("user");
		$ilCtrl->saveParameter($this, "user_page");
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser, $ilCtrl, $tpl, $ilTabs, $lng;
		
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
			
			default:
				$cmd = $this->ctrl->getCmd("showGeneralSettings");
				$this->$cmd();
				break;
		}
		return true;
	}

	/** 
	 * Called if the user pushes the submit button of the mail options form.
	 * Passes the post data to the mail options model instance to store them.
	 */
	public function saveMailOptions()
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 * @var $lng ilLanguage
		 * @var $rbacsystem ilRbacSystem
		 * @var $ilUser ilObjUser
		 * @var $ilSetting ilSetting
		 */
		global $ilUser, $lng, $ilTabs, $ilSetting, $rbacsystem;

		include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
		if(!$rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$lng->loadLanguageModule('mail');
		
		$this->__initSubTabs('showMailOptions');
		$ilTabs->activateTab('mail_settings');
		
		$this->setHeader();
		
		require_once 'Services/Mail/classes/class.ilMailOptions.php';
		$mailOptions = new ilMailOptions($ilUser->getId());
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1' && 
		   $ilSetting->get('usr_settings_disable_mail_incoming_mail') != '1')
		{
			$incoming_type = (int)$_POST['incoming_type'];
		}
		else
		{
			$incoming_type = $mailOptions->getIncomingType();
		}			
		
		$this->initMailOptionsForm();
		if($this->form->checkInput())
		{		
			$mailOptions->updateOptions(
				ilUtil::stripSlashes($_POST['signature']),
				(int)$_POST['linebreak'],
				$incoming_type,
				(int)$_POST['cronjob_notification']
			);
			
			ilUtil::sendSuccess($lng->txt('mail_options_saved'));			
		}
		
		if(!isset($_POST['incoming_type']))
		{
			$_POST['incoming_type'] = $mailOptions->getIncomingType();
		}
		
		$this->form->setValuesByPost();
		
		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	/** 
	 * Initialises the mail options form
	 */
	private function initMailOptionsForm()
	{
		global $ilCtrl, $ilSetting, $lng, $ilUser;	
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();
		
		$this->form->setFormAction($ilCtrl->getFormAction($this, 'saveMailOptions'));
		$this->form->setTitle($lng->txt('mail_settings'));
			
		// BEGIN INCOMING
		include_once 'Services/Mail/classes/class.ilMailOptions.php';
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1')
		{
			$options = array(
				IL_MAIL_LOCAL => $this->lng->txt('mail_incoming_local'), 
				IL_MAIL_EMAIL => $this->lng->txt('mail_incoming_smtp'),
				IL_MAIL_BOTH => $this->lng->txt('mail_incoming_both')
			);
			$si = new ilSelectInputGUI($lng->txt('mail_incoming'), 'incoming_type');
			$si->setOptions($options);
			if(!strlen(ilObjUser::_lookupEmail($ilUser->getId())) ||
			   $ilSetting->get('usr_settings_disable_mail_incoming_mail') == '1')
			{
				$si->setDisabled(true);	
			}
			$this->form->addItem($si);
		}
		
		// BEGIN LINEBREAK_OPTIONS
		$options = array();
		for($i = 50; $i <= 80; $i++)
		{
			$options[$i] = $i; 
		}	
		$si = new ilSelectInputGUI($lng->txt('linebreak'), 'linebreak');
		$si->setOptions($options);			
		$this->form->addItem($si);
		
		// BEGIN SIGNATURE
		$ta = new ilTextAreaInputGUI($lng->txt('signature'), 'signature');
		$ta->setRows(10);
		$ta->setCols(60);			
		$this->form->addItem($ta);
		
		// BEGIN CRONJOB NOTIFICATION
		if($ilSetting->get('mail_notification'))
		{
			$cb = new ilCheckboxInputGUI($lng->txt('cron_mail_notification'), 'cronjob_notification');			
			$cb->setInfo($lng->txt('mail_cronjob_notification_info'));
			$cb->setValue(1);
			$this->form->addItem($cb);
		}		
		
		$this->form->addCommandButton('saveMailOptions', $lng->txt('save'));
	}
	
	/** 
	 * Fetches data from model and loads this data into form
	 */
	private function setMailOptionsValuesByDB()
	{
		global $ilUser, $ilSetting;		
		
		require_once 'Services/Mail/classes/class.ilMailOptions.php';
		$mailOptions = new ilMailOptions($ilUser->getId());
		
		$data = array(
			'linebreak' => $mailOptions->getLinebreak(),
			'signature' => $mailOptions->getSignature(),
			'cronjob_notification' => $mailOptions->getCronjobNotification()
		);
		
		if($ilSetting->get('usr_settings_hide_mail_incoming_mail') != '1')
		{		
			$data['incoming_type'] = $mailOptions->getIncomingType();
		}
		
		$this->form->setValuesByArray($data);
	}

	/** 
	 * Called to display the mail options form
	 */
	public function showMailOptions()
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 * @var $lng ilLanguage
		 * @var $rbacsystem ilRbacSystem
		 */
		global $ilTabs, $lng, $rbacsystem;

		include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
		if(!$rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId()))
		{
			$this->ilias->raiseError($lng->txt('permission_denied'), $this->ilias->error_obj->MESSAGE);
		}
		
		$lng->loadLanguageModule('mail');
		
		$this->__initSubTabs('showMailOptions');
		$ilTabs->activateTab('mail_settings');

		$this->setHeader();

		$this->initMailOptionsForm();
		$this->setMailOptionsValuesByDB();

		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

/*	function showjsMath()
	{
		global $lng, $ilCtrl, $tpl, $ilUser;

		$this->__initSubTabs("showjsMath");
		$this->setHeader();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("jsmath_settings"));

		// Enable jsMath
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$jsMathSetting = new ilSetting("jsMath");
		$enable = new ilCheckboxInputGUI($lng->txt("jsmath_enable_user"), "enable");
		$checked = ($ilUser->getPref("js_math") === FALSE) ? $jsMathSetting->get("makedefault") : $ilUser->getPref("js_math");
		$enable->setChecked($checked);
		$enable->setInfo($lng->txt("jsmath_enable_user_desc"));
		$form->addItem($enable);

		$form->addCommandButton("savejsMath", $lng->txt("save"));
		$form->addCommandButton("showjsMath", $lng->txt("cancel"));

		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		$this->tpl->show();
	}

	function savejsMath()
	{
		global $ilCtrl, $ilUser;

		include_once "./Services/Administration/classes/class.ilSetting.php";
		$jsMathSetting = new ilSetting("jsMath");
		if ($jsMathSetting->get("enable"))
		{
			if ($_POST["enable"])
			{
				$ilUser->writePref("js_math", "1");
			}
			else
			{
				$ilUser->writePref("js_math", "0");
			}
		}
		$ilCtrl->redirect($this, "showjsMath");
	}
*/
	// init sub tabs
	function __initSubTabs($a_cmd)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $ilTabs, $ilSetting, $ilHelp, $rbacsystem, $ilUser;

		$ilHelp->setScreenIdComponent("user");
		
		$showPassword = ($a_cmd == 'showPassword') ? true : false;
		$showGeneralSettings = ($a_cmd == 'showGeneralSettings') ? true : false;
		$showMailOptions = ($a_cmd == 'showMailOptions') ? true : false;
//		$showjsMath = ($a_cmd == 'showjsMath') ? true : false;
		$showChatOptions = ($a_cmd == 'showChatOptions') ? true : false;

		// old profile

		// general settings
		$ilTabs->addTarget("general_settings", $this->ctrl->getLinkTarget($this, "showGeneralSettings"),
			"", "", "", $showGeneralSettings);

		// password
		if ($this->allowPasswordChange())
		{
			$ilTabs->addTarget("password", $this->ctrl->getLinkTarget($this, "showPassword"),
				"", "", "", $showPassword);
		}

		include_once 'Services/Mail/classes/class.ilMailGlobalServices.php';
		if($rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId()))
		{
			$ilTabs->addTarget("mail_settings", $this->ctrl->getLinkTarget($this, "showMailOptions"), "", "", "", $showMailOptions);
		}

		$chatSettings = new ilSetting('chatroom');
		$notificationSettings = new ilSetting('notifications');
		if(
			$chatSettings->get('chat_enabled', false) &&
			$notificationSettings->get('enable_osd', false) &&
			$chatSettings->get('play_invitation_sound', false)
		)
		{		
			$ilTabs->addTarget('chat_settings', $this->ctrl->getLinkTarget($this, 'showChatOptions'), '', '', '', $showChatOptions);
		}

		include_once "./Services/Administration/classes/class.ilSetting.php";
/*		$jsMathSetting = new ilSetting("jsMath");
		if ($jsMathSetting->get("enable"))
		{
			$ilTabs->addTarget("jsmath_extt_jsmath", $this->ctrl->getLinkTarget($this, "showjsMath"),
									 "", "", "", $showjsMath);
		}*/
		
		if((bool)$ilSetting->get('user_delete_own_account') &&
			$ilUser->getId() != SYSTEM_USER_ID)
		{
			$ilTabs->addTab("delacc", $this->lng->txt('user_delete_own_account'),
				$this->ctrl->getLinkTarget($this, "deleteOwnAccount1"));
		}
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	private function getChatSettingsForm()
	{
		/**
		 * @var $ilSetting ilSetting
		 * @var $lng       ilLanguage
		 */
		global $lng;

		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();

		$form->setFormAction($this->ctrl->getFormAction($this, 'saveChatOptions'));
		$form->setTitle($lng->txt("chat_settings"));

		$chb = new ilCheckboxInputGUI('', 'play_invitation_sound');
		$chb->setOptionTitle($this->lng->txt('play_invitation_sound'));
		$form->addItem($chb);

		$form->addCommandButton("saveChatOptions", $lng->txt("save"));

		return $form;
	}

	/**
	 *
	 */
	public function saveChatOptions()
	{
		/**
		 * @var $ilUser    ilObjUser
		 * @var $ilSetting ilSetting
		 * @var $lng       ilLanguage
		 * @var $ilCtrl    ilCtrl
		 */
		global $ilUser, $lng, $ilCtrl;

		$chatSettings         = new ilSetting('chatroom');
		$notificationSettings = new ilSetting('notifications');
		if(!(
			$chatSettings->get('chat_enabled', false) &&
			$notificationSettings->get('enable_osd', false) &&
			$chatSettings->get('play_invitation_sound', false)
		)
		)
		{
			$ilCtrl->redirect($this);
		}

		$form = $this->getChatSettingsForm();
		if(!$form->checkInput())
		{
			$this->showChatOptions($form);
			return;
		}

		$ilUser->setPref('chat_play_invitation_sound', (int)$form->getInput('play_invitation_sound'));
		$ilUser->writePrefs();

		ilUtil::sendSuccess($lng->txt('saved_successfully'));
		$this->showChatOptions($form);
	}

	/**
	 * Set header
	 */
	public function setHeader()
	{
		$this->tpl->setVariable('HEADER', $this->lng->txt('personal_settings'));
	}

	/**
	 * @param ilPropertyFormGUI $form
	 */
	public function showChatOptions(ilPropertyFormGUI $form = null)
	{
		/**
		 * @var $ilUser ilObjUser
		 * @var $ilCtrl ilCtrl
		 */
		global $ilUser, $ilCtrl;

		$chatSettings = new ilSetting('chatroom');
		$notificationSettings = new ilSetting('notifications');
		if(!(
			$chatSettings->get('chat_enabled', false) &&
			$notificationSettings->get('enable_osd', false) &&
			$chatSettings->get('play_invitation_sound', false)
		))
		{
			$ilCtrl->redirect($this);
		}

		$this->__initSubTabs('showChatOptions');
		$this->setHeader();

		if($form)
		{
			$form->setValuesByPost();
		}
		else
		{
			$form = $this->getChatSettingsForm();
			$form->setValuesByArray(array(
				'play_invitation_sound' => $ilUser->getPref('chat_play_invitation_sound')
			));
		}

		$this->tpl->setContent($form->getHTML());
		$this->tpl->show();
	}
	
	
	//
	//
	//	PASSWORD FORM
	//
	//

	/**
	 * @param bool $a_no_init
	 * @param bool $hide_form
	 */
	function showPassword($a_no_init = false, $hide_form = false)
	{
		global $ilTabs, $ilUser;
		
		$this->__initSubTabs("showPersonalData");
		$ilTabs->activateTab("password");

		$this->setHeader();
		// check whether password of user have to be changed
		// due to first login or password of user is expired
		if($ilUser->isPasswordChangeDemanded())
		{
			ilUtil::sendInfo(
				$this->lng->txt('password_change_on_first_login_demand')
			);
		}
		else if($ilUser->isPasswordExpired())
		{
			$msg          = $this->lng->txt('password_expired');
			$password_age = $ilUser->getPasswordAge();
			ilUtil::sendInfo(sprintf($msg, $password_age));
		}

		if (!$a_no_init && !$hide_form)
		{
			$this->initPasswordForm();
		}
		$this->tpl->setContent(!$hide_form ? $this->form->getHTML() : '');
		$this->tpl->show();
	}

	/**
	* Init password form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPasswordForm()
	{
		global $lng, $ilUser, $ilSetting;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// Check whether password change is allowed
		if ($this->allowPasswordChange())
		{
			// The current password needs to be checked for verification
			// unless the user uses Shibboleth authentication with additional
			// local authentication for WebDAV.
			//if (
			//	($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || !$ilSetting->get("shib_auth_allow_local"))
			//)
			if($ilUser->getAuthMode(true) == AUTH_LOCAL)
			{
				// current password
				$cpass = new ilPasswordInputGUI($lng->txt("current_password"), "current_password");
				$cpass->setRetype(false);
				$cpass->setSkipSyntaxCheck(true);
				// only if a password exists.
				if($ilUser->getPasswd())
				{
					$cpass->setRequired(true);
				}
				$this->form->addItem($cpass);
			}
			
			// new password
			$ipass = new ilPasswordInputGUI($lng->txt("desired_password"), "new_password");
			$ipass->setRequired(true);
			$ipass->setInfo(ilUtil::getPasswordRequirementsInfo());

			if ($ilSetting->get("passwd_auto_generate") == 1)	// auto generation list
			{
				$ipass->setPreSelection(true);
				
				$this->form->addItem($ipass);
				$this->form->addCommandButton("savePassword", $lng->txt("save"));
				$this->form->addCommandButton("showPassword", $lng->txt("new_list_password"));
			}
			else  								// user enters password
			{
				$this->form->addItem($ipass);
				$this->form->addCommandButton("savePassword", $lng->txt("save"));
			}
			
			switch ($ilUser->getAuthMode(true))
			{
				case AUTH_LOCAL :
					$this->form->setTitle($lng->txt("chg_password"));
					break;
					
				case AUTH_SHIBBOLETH :
				case AUTH_CAS:
					require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
					if (ilDAVServer::_isActive())
					{
						$this->form->setTitle($lng->txt("chg_ilias_and_webfolder_password"));
					}
					else
					{
						$this->form->setTitle($lng->txt("chg_ilias_password"));
					}
					break;
				default :
					$this->form->setTitle($lng->txt("chg_ilias_password"));
					break;
			}
			$this->form->setFormAction($this->ctrl->getFormAction($this));
		}
	}
	
	/**
	* Check, whether password change is allowed for user
	*/
	function allowPasswordChange()
	{
		global $ilUser, $ilSetting;
		
		
		return ilAuthUtils::isPasswordModificationEnabled($ilUser->getAuthMode(true));
		
		// Moved to ilAuthUtils
		
		// do nothing if auth mode is not local database
		if ($ilUser->getAuthMode(true) != AUTH_LOCAL &&
			($ilUser->getAuthMode(true) != AUTH_CAS || !$ilSetting->get("cas_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || !$ilSetting->get("shib_auth_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_SOAP || !$ilSetting->get("soap_auth_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_OPENID)
			)
		{
			return false;
		}
		if (!$this->userSettingVisible('password') ||
			$this->ilias->getSetting('usr_settings_disable_password'))
		{
			return false;
		}		
		return true;
	}
	
	/**
	* Save password form
	*
	*/
	public function savePassword()
	{
		global $tpl, $lng, $ilCtrl, $ilUser, $ilSetting;
	
		// normally we should not end up here
		if (!$this->allowPasswordChange())
		{
			$ilCtrl->redirect($this, "showPersonalData");
			return;
		}
		
		$this->initPasswordForm();
		if ($this->form->checkInput())
		{
			$cp = $this->form->getItemByPostVar("current_password");
			$np = $this->form->getItemByPostVar("new_password");
			$error = false;
			
			// The old password needs to be checked for verification
			// unless the user uses Shibboleth authentication with additional
			// local authentication for WebDAV.
			#if ($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || ! $ilSetting->get("shib_auth_allow_local"))
			if($ilUser->getAuthMode(true) == AUTH_LOCAL)
			{
				require_once 'Services/User/classes/class.ilUserPasswordManager.php';
				if(!ilUserPasswordManager::getInstance()->verifyPassword($ilUser, ilUtil::stripSlashes($_POST['current_password'])))
				{
					$error = true;
					$cp->setAlert($this->lng->txt('passwd_wrong'));
				}
			}

			// select password from auto generated passwords
			if ($this->ilias->getSetting("passwd_auto_generate") == 1 &&
				(!ilUtil::isPassword($_POST["new_password"])))
			{
				$error = true;
				$np->setAlert($this->lng->txt("passwd_not_selected"));
			}
				
	
			if ($this->ilias->getSetting("passwd_auto_generate") != 1 &&
				!ilUtil::isPassword($_POST["new_password"],$custom_error))
			{
				$error = true;
				if ($custom_error != '')
				{
					$np->setAlert($custom_error);
				}
				else
				{
					$np->setAlert($this->lng->txt("passwd_invalid"));
				}
			}
			$error_lng_var = '';
			if(
				$this->ilias->getSetting("passwd_auto_generate") != 1 &&
				!ilUtil::isPasswordValidForUserContext($_POST["new_password"], $ilUser, $error_lng_var)
			)
			{
				ilUtil::sendFailure($this->lng->txt('form_input_not_valid'));
				$np->setAlert($this->lng->txt($error_lng_var));
				$error = true;
			}
			if ($this->ilias->getSetting("passwd_auto_generate") != 1 &&
				($ilUser->isPasswordExpired() || $ilUser->isPasswordChangeDemanded()) &&
				($_POST["current_password"] == $_POST["new_password"]))
			{
				$error = true;
				$np->setAlert($this->lng->txt("new_pass_equals_old_pass"));
			}
			
			if (!$error)
			{
				$ilUser->resetPassword($_POST["new_password"], $_POST["new_password"]);
				if ($_POST["current_password"] != $_POST["new_password"])
				{
					$ilUser->setLastPasswordChangeToNow();
				}

				if(ilSession::get('orig_request_target'))
				{
					ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
					$target = ilSession::get('orig_request_target');
					ilSession::set('orig_request_target', '');
					ilUtil::redirect($target);
				}
				else
				{
					ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
					$this->showPassword(true, true);
					return;
				}
			}
		}
		$this->form->setValuesByPost();
		$this->showPassword(true);
	}
	
	//
	//
	//	GENERAL SETTINGS FORM
	//
	//

	/**
	 * Returns TRUE if working with the given
	 * user setting is allowed, FALSE otherwise
	 */
	function workWithUserSetting($setting)
	{
		$result = TRUE;
		if ($this->settings["usr_settings_hide_".$setting] == 1)
		{
			$result = FALSE;
		}
		if ($this->settings["usr_settings_disable_".$setting] == 1)
		{
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * Returns TRUE if user setting is
	 * visible, FALSE otherwise
	 */
	function userSettingVisible($setting)
	{
		$result = TRUE;
		if (isset($this->settings["usr_settings_hide_".$setting]) &&
			$this->settings["usr_settings_hide_".$setting] == 1)
		{
			$result = FALSE;
		}
		return $result;
	}

	/**
	 * Returns TRUE if user setting is
	 * enabled, FALSE otherwise
	 */
	function userSettingEnabled($setting)
	{
		$result = TRUE;
		if ($this->settings["usr_settings_disable_".$setting] == 1)
		{
			$result = FALSE;
		}
		return $result;
	}

	/**
	* General settings form.
	*/
	function showGeneralSettings($a_no_init = false)
	{
		global $ilTabs, $ilToolbar, $ilCtrl;
		
		// test to other base class
//		$ilToolbar->addButton("test",
//			$ilCtrl->getLinkTargetByClass(array("ilmailgui","ilmailformgui"), "mailUser"));
		
		$this->__initSubTabs("showPersonalData");
		$ilTabs->activateTab("general_settings");

		$this->setHeader();

		if (!$a_no_init)
		{
			$this->initGeneralSettingsForm();
		}
		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	/**
	* Init general settings form.
	*
	*/
	public function initGeneralSettingsForm()
	{
		global $lng, $ilUser, $styleDefinition, $ilSetting;
		
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// language
		if ($this->userSettingVisible("language"))
		{
			$languages = $this->lng->getInstalledLanguages();
			$options = array();
			foreach($languages as $lang_key)
			{
				$options[$lang_key] = ilLanguage::_lookupEntry($lang_key,"meta", "meta_l_".$lang_key);
			}
			
			$si = new ilSelectInputGUI($this->lng->txt("language"), "language");
			$si->setOptions($options);
			$si->setValue($ilUser->getLanguage());
			$si->setDisabled($ilSetting->get("usr_settings_disable_language"));
			$this->form->addItem($si);
		}

		// skin/style
		include_once("./Services/Style/classes/class.ilObjStyleSettings.php");
		if ($this->userSettingVisible("skin_style"))
		{
			$templates = $styleDefinition->getAllTemplates();
			if (is_array($templates))
			{ 
				$si = new ilSelectInputGUI($this->lng->txt("skin_style"), "skin_style");
				
				$options = array();
				foreach($templates as $template)
				{
					// get styles information of template
					$styleDef = new ilStyleDefinition($template["id"]);
					$styleDef->startParsing();
					$styles = $styleDef->getStyles();

					foreach($styles as $style)
					{
						if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
						{
							continue;
						}

						$options[$template["id"].":".$style["id"]] =
							$styleDef->getTemplateName()." / ".$style["name"];
					}
				}
				$si->setOptions($options);
				$si->setValue($ilUser->skin.":".$ilUser->prefs["style"]);
				$si->setDisabled($ilSetting->get("usr_settings_disable_skin_style"));
				$this->form->addItem($si);
			}
		}

		// screen reader optimization
		if ($this->userSettingVisible("screen_reader_optimization"))
		{ 
			$cb = new ilCheckboxInputGUI($this->lng->txt("user_screen_reader_optimization"), "screen_reader_optimization");
			$cb->setChecked($ilUser->prefs["screen_reader_optimization"]);
			$cb->setDisabled($ilSetting->get("usr_settings_disable_screen_reader_optimization"));
			$cb->setInfo($this->lng->txt("user_screen_reader_optimization_info"));
			$this->form->addItem($cb);
		}

		// hits per page
		if ($this->userSettingVisible("hits_per_page"))
		{
			$si = new ilSelectInputGUI($this->lng->txt("hits_per_page"), "hits_per_page");
			
			$hits_options = array(10,15,20,30,40,50,100,9999);
			$options = array();

			foreach($hits_options as $hits_option)
			{
				$hstr = ($hits_option == 9999)
					? $this->lng->txt("no_limit")
					: $hits_option;
				$options[$hits_option] = $hstr;
			}
			$si->setOptions($options);
			$si->setValue($ilUser->prefs["hits_per_page"]);
			$si->setDisabled($ilSetting->get("usr_settings_disable_hits_per_page"));
			$this->form->addItem($si);
		}

		// Users Online
		if ($this->userSettingVisible("show_users_online"))
		{
			$si = new ilSelectInputGUI($this->lng->txt("show_users_online"), "show_users_online");
			
			$options = array(
				"y" => $this->lng->txt("users_online_show_y"),
				"associated" => $this->lng->txt("users_online_show_associated"),
				"n" => $this->lng->txt("users_online_show_n"));
			$si->setOptions($options);
			$si->setValue($ilUser->prefs["show_users_online"]);
			$si->setDisabled($ilSetting->get("usr_settings_disable_show_users_online"));
			$this->form->addItem($si);
		}

		// Store last visited
		$lv = new ilSelectInputGUI($this->lng->txt("user_store_last_visited"), "store_last_visited");
		$options = array(
			0 => $this->lng->txt("user_lv_keep_entries"),
			1 => $this->lng->txt("user_lv_keep_only_for_session"),
			2 => $this->lng->txt("user_lv_do_not_store"));
		$lv->setOptions($options);
		$lv->setValue((int) $ilUser->prefs["store_last_visited"]);
		$this->form->addItem($lv);

		// hide_own_online_status
		if ($this->userSettingVisible("hide_own_online_status"))
		{ 
			$cb = new ilCheckboxInputGUI($this->lng->txt("hide_own_online_status"), "hide_own_online_status");
			$cb->setChecked($ilUser->prefs["hide_own_online_status"] == "y");
			$cb->setDisabled($ilSetting->get("usr_settings_disable_hide_own_online_status"));
			$this->form->addItem($cb);
		}
		
		include_once 'Services/Authentication/classes/class.ilSessionReminder.php';
		if(ilSessionReminder::isGloballyActivated())
		{
			$cb = new ilCheckboxInputGUI($this->lng->txt('session_reminder'), 'session_reminder_enabled');
			$cb->setInfo($this->lng->txt('session_reminder_info'));
			$cb->setValue(1);
			$cb->setChecked((int)$ilUser->getPref('session_reminder_enabled'));

			$expires = ilSession::getSessionExpireValue();
			$lead_time_gui = new ilNumberInputGUI($this->lng->txt('session_reminder_lead_time'), 'session_reminder_lead_time');
			$lead_time_gui->setInfo(sprintf($this->lng->txt('session_reminder_lead_time_info'), ilFormat::_secondsToString($expires, true)));

			$min_value = ilSessionReminder::MIN_LEAD_TIME;
			$max_value = max($min_value, ((int)$expires / 60) - 1);

			$current_user_value = $ilUser->getPref('session_reminder_lead_time');
			if($current_user_value < $min_value ||
			   $current_user_value > $max_value)
			{
				$current_user_value = ilSessionReminder::SUGGESTED_LEAD_TIME;
			}
			$value = min(
				max(
					$min_value, $current_user_value
				),
				$max_value
			);

			$lead_time_gui->setValue($value);
			$lead_time_gui->setSize(3);
			$lead_time_gui->setMinValue($min_value);
			$lead_time_gui->setMaxValue($max_value);
			$cb->addSubItem($lead_time_gui);

			$this->form->addItem($cb);
		}

		// calendar settings (copied here to be reachable when calendar is inactive)
		// they cannot be hidden/deactivated

		include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
		include_once('Services/Calendar/classes/class.ilCalendarUtil.php');
		$lng->loadLanguageModule("dateplaner");
		$user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());

		$select = new ilSelectInputGUI($lng->txt('cal_user_timezone'),'timezone');
		$select->setOptions(ilCalendarUtil::_getShortTimeZoneList());
		$select->setInfo($lng->txt('cal_timezone_info'));
		$select->setValue($user_settings->getTimeZone());
		$this->form->addItem($select);

		$year = date("Y");
		$select = new ilSelectInputGUI($lng->txt('cal_user_date_format'),'date_format');
		$select->setOptions(array(
			ilCalendarSettings::DATE_FORMAT_DMY => '31.10.'.$year,
			ilCalendarSettings::DATE_FORMAT_YMD => $year."-10-31",
			ilCalendarSettings::DATE_FORMAT_MDY => "10/31/".$year));
		$select->setInfo($lng->txt('cal_date_format_info'));
		$select->setValue($user_settings->getDateFormat());
		$this->form->addItem($select);

		$select = new ilSelectInputGUI($lng->txt('cal_user_time_format'),'time_format');
		$select->setOptions(array(
			ilCalendarSettings::TIME_FORMAT_24 => '13:00',
			ilCalendarSettings::TIME_FORMAT_12 => '1:00pm'));
		$select->setInfo($lng->txt('cal_time_format_info'));
	    $select->setValue($user_settings->getTimeFormat());
		$this->form->addItem($select);
		
		
		// starting point	
		include_once "Services/User/classes/class.ilUserUtil.php";
		if(ilUserUtil::hasPersonalStartingPoint())
		{
			$this->lng->loadLanguageModule("administration");
			$si = new ilRadioGroupInputGUI($this->lng->txt("adm_user_starting_point"), "usr_start");
			$si->setRequired(true);
			$si->setInfo($this->lng->txt("adm_user_starting_point_info"));
			foreach(ilUserUtil::getPossibleStartingPoints() as $value => $caption)
			{
				$si->addOption(new ilRadioOption($caption, $value));
			}
			$si->setValue(ilUserUtil::getPersonalStartingPoint());		
			$this->form->addItem($si);
						
			// starting point: repository object
			$repobj = new ilRadioOption($lng->txt("adm_user_starting_point_object"), ilUserUtil::START_REPOSITORY_OBJ);
			$repobj_id = new ilTextInputGUI($lng->txt("adm_user_starting_point_ref_id"), "usr_start_ref_id");
			$repobj_id->setRequired(true);
			$repobj_id->setSize(5);
			if($si->getValue() == ilUserUtil::START_REPOSITORY_OBJ)
			{
				$start_ref_id = ilUserUtil::getPersonalStartingObject();
				$repobj_id->setValue($start_ref_id);
				if($start_ref_id)
				{
					$start_obj_id = ilObject::_lookupObjId($start_ref_id);
					if($start_obj_id)
					{
						$repobj_id->setInfo($lng->txt("obj_".ilObject::_lookupType($start_obj_id)).
							": ".ilObject::_lookupTitle($start_obj_id));
					}
				}
			}		
			$repobj->addSubItem($repobj_id);
			$si->addOption($repobj);
		}		
		
		// selector for unicode characters
		global $ilSetting;
		if ($ilSetting->get('char_selector_availability') > 0)
		{
			require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
			$char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_USER);
			$char_selector->getConfig()->setAvailability($ilUser->getPref('char_selector_availability'));
			$char_selector->getConfig()->setDefinition($ilUser->getPref('char_selector_definition'));
			$char_selector->addFormProperties($this->form);
			$char_selector->setFormValues($this->form);
		}
		
		$this->form->addCommandButton("saveGeneralSettings", $lng->txt("save"));
		$this->form->setTitle($lng->txt("general_settings"));
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	 * Save general settings
	 */
	public function saveGeneralSettings()
	{
		global $tpl, $lng, $ilCtrl, $ilUser;
	
		$this->initGeneralSettingsForm();
		if ($this->form->checkInput())
		{	
			if ($this->workWithUserSetting("skin_style"))
			{
				//set user skin and style
				if ($_POST["skin_style"] != "")
				{
					$sknst = explode(":", $_POST["skin_style"]);

					if ($ilUser->getPref("style") != $sknst[1] ||
						$ilUser->getPref("skin") != $sknst[0])
					{
						$ilUser->setPref("skin", $sknst[0]);
						$ilUser->setPref("style", $sknst[1]);
					}
				}
			}

			// language
			if ($this->workWithUserSetting("language"))
			{
				$ilUser->setLanguage($_POST["language"]);
			}
			
			// hits per page
			if ($this->workWithUserSetting("hits_per_page"))
			{
				if ($_POST["hits_per_page"] != "")
				{
					$ilUser->setPref("hits_per_page",$_POST["hits_per_page"]);
				}
			}

			// set show users online
			if ($this->workWithUserSetting("show_users_online"))
			{
				$ilUser->setPref("show_users_online", $_POST["show_users_online"]);
			}
			
			// store last visited?
			global $ilNavigationHistory;
			$ilUser->setPref("store_last_visited", (int) $_POST["store_last_visited"]);
			if ((int) $_POST["store_last_visited"] > 0)
			{
				$ilNavigationHistory->deleteDBEntries();
				if ((int) $_POST["store_last_visited"] == 2)
				{
					$ilNavigationHistory->deleteSessionEntries();
				}
			}

			// set hide own online_status
			if ($this->workWithUserSetting("hide_own_online_status"))
			{
				if ($_POST["hide_own_online_status"] == 1)
				{
					$ilUser->setPref("hide_own_online_status","y");
				}
				else
				{
					$ilUser->setPref("hide_own_online_status","n");
				}
			}

			// set show users online
			if ($this->workWithUserSetting("screen_reader_optimization"))
			{
				$ilUser->setPref("screen_reader_optimization", $_POST["screen_reader_optimization"]);
			}
			
			// session reminder
			include_once 'Services/Authentication/classes/class.ilSessionReminder.php';
			if(ilSessionReminder::isGloballyActivated())
			{
				$ilUser->setPref('session_reminder_enabled', (int)$this->form->getInput('session_reminder_enabled'));
				$ilUser->setPref('session_reminder_lead_time', $this->form->getInput('session_reminder_lead_time'));
			}

			// starting point	
			include_once "Services/User/classes/class.ilUserUtil.php";
			if(ilUserUtil::hasPersonalStartingPoint())
			{
				ilUserUtil::setPersonalStartingPoint($this->form->getInput('usr_start'), 
					$this->form->getInput('usr_start_ref_id'));
			}

			// selector for unicode characters
			global $ilSetting;
			if ($ilSetting->get('char_selector_availability') > 0)
			{
				require_once 'Services/UIComponent/CharSelector/classes/class.ilCharSelectorGUI.php';
				$char_selector = new ilCharSelectorGUI(ilCharSelectorConfig::CONTEXT_USER);
				$char_selector->getFormValues($this->form);
				$ilUser->setPref('char_selector_availability', $char_selector->getConfig()->getAvailability());
				$ilUser->setPref('char_selector_definition', $char_selector->getConfig()->getDefinition());
			}

			$ilUser->update();

			// calendar settings
			include_once('Services/Calendar/classes/class.ilCalendarUserSettings.php');
			$user_settings = ilCalendarUserSettings::_getInstanceByUserId($ilUser->getId());
			$user_settings->setTimeZone($this->form->getInput("timezone"));
			$user_settings->setDateFormat((int)$this->form->getInput("date_format"));
			$user_settings->setTimeFormat((int)$this->form->getInput("time_format"));
			$user_settings->save();
						
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showGeneralSettings");
		}

		$this->form->setValuesByPost();
		$this->showGeneralSettings(true);
	}
	
	/**
	 * Delete own account dialog - 1st confirmation
	 */
	protected function deleteOwnAccount1()
	{	
		global $ilTabs, $ilToolbar, $ilUser, $ilSetting;
		
		if(!(bool)$ilSetting->get('user_delete_own_account') ||
			$ilUser->getId() == SYSTEM_USER_ID)
		{
			$this->ctrl->redirect($this, "showGeneralSettings");
		}
		
		// too make sure
		$ilUser->removeDeletionFlag();		
		
		$this->setHeader();
		$this->__initSubTabs("deleteOwnAccount");
		$ilTabs->activateTab("delacc");
		
		ilUtil::sendInfo($this->lng->txt('user_delete_own_account_info'));
		$ilToolbar->addButton($this->lng->txt('btn_next'),
			$this->ctrl->getLinkTarget($this, 'deleteOwnAccount2'));
		
		$this->tpl->show();
	}
	
	/**
	 * Delete own account dialog - login redirect
	 */
	protected function deleteOwnAccount2()
	{	
		global $ilTabs, $ilUser, $ilSetting;
		
		if(!(bool)$ilSetting->get('user_delete_own_account') ||
			$ilUser->getId() == SYSTEM_USER_ID)
		{
			$this->ctrl->redirect($this, "showGeneralSettings");
		}
		
		$this->setHeader();
		$this->__initSubTabs("deleteOwnAccount");
		$ilTabs->activateTab("delacc");
		
		include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
		$cgui = new ilConfirmationGUI();		
		$cgui->setHeaderText($this->lng->txt('user_delete_own_account_logout_confirmation'));
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt("cancel"), "abortDeleteOwnAccount");
		$cgui->setConfirm($this->lng->txt("user_delete_own_account_logout_button"), "deleteOwnAccountLogout");		
		$this->tpl->setContent($cgui->getHTML());			
		$this->tpl->show();	
	}
	
	protected function abortDeleteOwnAccount()
	{
		global $ilCtrl, $ilUser;
		
		$ilUser->removeDeletionFlag();			
		
		ilUtil::sendInfo($this->lng->txt("user_delete_own_account_aborted"), true);
		$ilCtrl->redirect($this, "showGeneralSettings");
	}
	
	protected function deleteOwnAccountLogout()
	{
		global $ilAuth, $ilUser;
				
		// we are setting the flag and ending the session in the same step
		
		$ilUser->activateDeletionFlag();				
				
		// see ilStartupGUI::showLogout()
		ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);		
		$ilAuth->logout();
		session_destroy();
		
		ilUtil::redirect("login.php?target=usr_".md5("usrdelown"));		
	}
	
	/**
	 * Delete own account dialog - final confirmation
	 */
	protected function deleteOwnAccount3()
	{	
		global $ilTabs, $ilUser, $ilSetting;	
		
		if(!(bool)$ilSetting->get('user_delete_own_account') ||
			$ilUser->getId() == SYSTEM_USER_ID ||
			!$ilUser->hasDeletionFlag())
		{
			$this->ctrl->redirect($this, "showGeneralSettings");
		}
	
		$this->setHeader();
		$this->__initSubTabs("deleteOwnAccount");
		$ilTabs->activateTab("delacc");

		include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
		$cgui = new ilConfirmationGUI();		
		$cgui->setHeaderText($this->lng->txt('user_delete_own_account_final_confirmation'));
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt("cancel"), "abortDeleteOwnAccount");
		$cgui->setConfirm($this->lng->txt("confirm"), "deleteOwnAccount4");		
		$this->tpl->setContent($cgui->getHTML());			
		$this->tpl->show();	
	}
	
	/**
	 * Delete own account dialog - action incl. notification email
	 */
	protected function deleteOwnAccount4()
	{	
		global $ilUser, $ilAuth, $ilSetting, $ilLog;
		
		if(!(bool)$ilSetting->get('user_delete_own_account') ||
			$ilUser->getId() == SYSTEM_USER_ID ||
			!$ilUser->hasDeletionFlag())
		{
			$this->ctrl->redirect($this, "showGeneralSettings");
		}
		
		// build notification
		
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();
		$ntf->setLangModules(array("user"));		
		$ntf->addAdditionalInfo("profile", $ilUser->getProfileAsString($this->lng), true);		
		
		// mail message
		ilDatePresentation::setUseRelativeDates(false);
		$ntf->setIntroductionDirect(
			sprintf($this->lng->txt("user_delete_own_account_email_body"), 
				$ilUser->getLogin(), 
				ILIAS_HTTP_PATH, 
				ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX))));
		
		$message = $ntf->composeAndGetMessage($ilUser->getId(), null, null, true);
		$subject = $this->lng->txt("user_delete_own_account_email_subject");	
		
		
		// send notification
		
		include_once "Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail(ANONYMOUS_USER_ID);
		
		$user_email = $ilUser->getEmail();		
		$admin_mail = $ilSetting->get("user_delete_own_account_email");		
		
		// to user, admin as bcc
		if($user_email)
		{											
			$mail->sendMimeMail($user_email, null, $admin_mail, $subject, $message, null, true);		
		}
		// admin only
		else if($admin_mail)
		{
			$mail->sendMimeMail($admin_mail, null, null, $subject, $message, null, true);		
		}
		
		$ilLog->write("Account deleted: ".$ilUser->getLogin()." (".$ilUser->getId().")");
				
		$ilUser->delete();

		// terminate session
		$ilAuth->logout();
		session_destroy();		
		
		ilUtil::redirect("login.php?accdel=1");		 		
	}
}
?>
