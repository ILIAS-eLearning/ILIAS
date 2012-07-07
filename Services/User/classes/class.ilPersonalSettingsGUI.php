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
		$lng->loadLanguageModule("jsmath");
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
				
				// check whether password of user have to be changed
				// due to first login or password of user is expired
				if( $ilUser->isPasswordChangeDemanded() && $cmd != 'savePassword' )
				{
					$cmd = 'showPassword';

					ilUtil::sendInfo(
						$this->lng->txt('password_change_on_first_login_demand'), true
					);
				}
				elseif( $ilUser->isPasswordExpired() && $cmd != 'savePassword' )
				{
					$cmd = 'showPassword';

					$msg = $this->lng->txt('password_expired');
					$password_age = $ilUser->getPasswordAge();

					ilUtil::sendInfo( sprintf($msg,$password_age), true );
				}

				$this->$cmd();
				break;
		}
		return true;
	}



	/**
	* change user password
	*/
	function changeUserPassword()
	{
		global $ilUser, $ilSetting;

		// do nothing if auth mode is not local database
		if ($ilUser->getAuthMode(true) != AUTH_LOCAL &&
			($ilUser->getAuthMode(true) != AUTH_CAS || !$ilSetting->get("cas_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || !$ilSetting->get("shib_auth_allow_local")) &&
			($ilUser->getAuthMode(true) != AUTH_SOAP || !$ilSetting->get("soap_auth_allow_local"))
			)
		{
			$this->password_error = $this->lng->txt("not_changeable_for_non_local_auth");
		}

		// select password from auto generated passwords
		if ($this->ilias->getSetting("passwd_auto_generate") == 1)
		{
			// The old password needs to be checked for verification
			// unless the user uses Shibboleth authentication with additional
			// local authentication for WebDAV.
			if ($ilUser->getAuthMode(true) != AUTH_SHIBBOLETH || ! $ilSetting->get("shib_auth_allow_local"))
			{
				// check old password
				if (md5($_POST["current_password"]) != $ilUser->getPasswd())
				{
					$this->password_error = $this->lng->txt("passwd_wrong");
				}
			}
			// validate transmitted password
			if (!ilUtil::isPassword($_POST["new_passwd"]))
			{
				$this->password_error = $this->lng->txt("passwd_not_selected");
			}

			if (empty($this->password_error))
			{
				ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
				$ilUser->updatePassword($_POST["current_password"], $_POST["new_passwd"], $_POST["new_passwd"]);
			}
		}
		else
		{
			// check old password
			if (md5($_POST["current_password"]) != $ilUser->getPasswd())
			{
				$this->password_error = $this->lng->txt("passwd_wrong");
			}
			// check new password
			else if ($_POST["desired_password"] != $_POST["retype_password"])
			{
				$this->password_error = $this->lng->txt("passwd_not_match");
			}
			// validate password
			else if (!ilUtil::isPassword($_POST["desired_password"],$custom_error))
			{
				if( $custom_error != '' )
						$this->password_error = $custom_error;
				else	$this->password_error = $this->lng->txt("passwd_invalid");
			}
			else if ($_POST["current_password"] != "" and empty($this->password_error))
			{
				if( $ilUser->isPasswordExpired() || $ilUser->isPasswordChangeDemanded() )
				{
					if( $_POST["current_password"] != $_POST["desired_password"] )
					{
						if( $ilUser->updatePassword($_POST["current_password"], $_POST["desired_password"], $_POST["retype_password"]) )
						{
							ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
 							$ilUser->setLastPasswordChangeToNow();
						}
					}
					else
					{
						$this->password_error = $this->lng->txt("new_pass_equals_old_pass");
					}
				}
				else
				{
					ilUtil::sendSuccess($this->lng->txt("saved_successfully"));
					$ilUser->updatePassword($_POST["current_password"], $_POST["desired_password"], $_POST["retype_password"]);
					$ilUser->setLastPasswordChangeToNow();
				}
			}
		}

		$this->saveProfile();
	}


	/** 
	 * Called if the user pushes the submit button of the mail options form.
	 * Passes the post data to the mail options model instance to store them.
	 */
	public function saveMailOptions()
	{
		global $ilUser, $lng, $ilTabs, $ilSetting;
		
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
		global $ilTabs, $lng;
		
		$lng->loadLanguageModule('mail');
		
		$this->__initSubTabs('showMailOptions');
		$ilTabs->activateTab('mail_settings');

		$this->setHeader();

		$this->initMailOptionsForm();
		$this->setMailOptionsValuesByDB();

		$this->tpl->setContent($this->form->getHTML());
		$this->tpl->show();
	}

	function showjsMath()
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

	// init sub tabs
	function __initSubTabs($a_cmd)
	{
		global $ilTabs, $ilSetting, $ilHelp;

		$ilHelp->setScreenIdComponent("user");
		
		$showPassword = ($a_cmd == 'showPassword') ? true : false;
		$showGeneralSettings = ($a_cmd == 'showGeneralSettings') ? true : false;
		$showMailOptions = ($a_cmd == 'showMailOptions') ? true : false;
		$showjsMath = ($a_cmd == 'showjsMath') ? true : false;
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
			

		$ilTabs->addTarget("mail_settings", $this->ctrl->getLinkTarget($this, "showMailOptions"),
								 "", "", "", $showMailOptions);		

		if(((int)$ilSetting->get('chat_sound_status') &&
		    ((int)$ilSetting->get('chat_new_invitation_sound_status') || 
		     (int)$ilSetting->get('chat_new_message_sound_status'))) ||
		   (int)$ilSetting->get('chat_message_notify_status') == 1)
		{		
			$ilTabs->addTarget("chat_settings", $this->ctrl->getLinkTarget($this, "showChatOptions"),
										 "", "", "", $showChatOptions);
		}
		
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$jsMathSetting = new ilSetting("jsMath");
		if ($jsMathSetting->get("enable"))
		{
			$ilTabs->addTarget("jsmath_extt_jsmath", $this->ctrl->getLinkTarget($this, "showjsMath"),
									 "", "", "", $showjsMath);
		}
		
		if((bool)$ilSetting->get('user_delete_own_account'))
		{
			$ilTabs->addTab("delacc", $this->lng->txt('user_delete_own_account'),
				$this->ctrl->getLinkTarget($this, "deleteOwnAccount1"));
		}
	}


	
	private function getChatSettingsForm()
	{
		global $ilCtrl, $ilSetting, $lng;
		
		$lng->loadLanguageModule('chat');
		
		include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		
		$form->setFormAction($this->ctrl->getFormAction($this, 'saveChatOptions'));
		$form->setTitle($lng->txt("chat_settings"));
		
		if((int)$ilSetting->get('chat_message_notify_status'))
		{
			// chat message notification in ilias
			//$rg_parent = new ilRadioGroupInputGUI($this->lng->txt('chat_message_notify'), 'chat_message_notify_status');
			//$rg_parent->setValue(0);

			$ro_parent = new ilCheckboxInputGUI($this->lng->txt('chat_message_notify_activate'), 'chat_message_notify_activate');
                        //$rg_parent->addOption($ro_parent);
                        #$ro_parent->setValue(0);

                        if((int)$ilSetting->get('chat_sound_status') &&
                           ((int)$ilSetting->get('chat_new_invitation_sound_status') ||
                            (int)$ilSetting->get('chat_new_message_sound_status')))
                        {
                                // sound activation/deactivation for new chat invitations and messages
                                #$rg = new ilRadioGroupInputGUI($this->lng->txt('chat_sounds'), 'chat_sound_status');
                                #$rg->setValue(0);
                                #$ro = new ilCheckboxInputGUI($this->lng->txt('chat_sound_status_activate'), 1);
                                if((int)$ilSetting->get('chat_new_invitation_sound_status'))
                                {
                                        $chb = new ilCheckboxInputGUI('', 'chat_new_invitation_sound_status');
                                        $chb->setOptionTitle($this->lng->txt('chat_new_invitation_sound_status'));
                                        $chb->setChecked(false);
                                        $ro_parent->addSubItem($chb);
                                }
                                
                                if((int)$ilSetting->get('chat_new_message_sound_status'))
                                {
                                        $chb = new ilCheckBoxInputGUI('','chat_new_message_sound_status');
                                        $chb->setOptionTitle($this->lng->txt('chat_new_message_sound_status'));
                                        $chb->setChecked(false);
                                        $ro_parent->addSubItem($chb);
                                }
                                #$rg->addOption($ro);

                                //$ro = new ilRadioOption($this->lng->txt('chat_sound_status_deactivate'), 0);
                                //$rg->addOption($ro);

                                #$ro_parent->addSubItem($rg);
                                //$form->addItem($rg);
                        }


                        #$ro_parent = new ilRadioOption($this->lng->txt('chat_message_notify_deactivate'), 0);
        		#$rg_parent->addOption($ro_parent);
			$form->addItem($ro_parent);
		}
		
		$form->addCommandButton("saveChatOptions", $lng->txt("save"));
		return $form;
	}
	
	public function saveChatOptions()
	{
		global $ilUser, $ilSetting, $lng;
		
		if((int)$ilSetting->get('chat_message_notify_status'))
		{
			$ilUser->setPref('chat_message_notify_status', (int)$_POST['chat_message_notify_activate']);
		}
		
		if((int)$ilSetting->get('chat_sound_status') &&
		   ((int)$ilSetting->get('chat_new_invitation_sound_status') || 
		    (int)$ilSetting->get('chat_new_message_sound_status')))
		{
			$ilUser->setPref('chat_sound_status', (int)$_POST['chat_sound_status']);
			if((int)$ilSetting->get('chat_new_invitation_sound_status'))
			{
				$ilUser->setPref('chat_new_invitation_sound_status', (int)$_POST['chat_new_invitation_sound_status']);
			}
			if((int)$ilSetting->get('chat_new_message_sound_status'))
			{
				$ilUser->setPref('chat_new_message_sound_status', (int)$_POST['chat_new_message_sound_status']);
			}
		}
		
		$ilUser->writePrefs();		
		
		ilUtil::sendSuccess($lng->txt('saved'));
		
		$this->showChatOptions(true);
	}

	/**
	 * Set header
	 */
	function setHeader()
	{
//		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.png"), "");
		$this->tpl->setVariable('HEADER', $this->lng->txt('personal_settings'));
	}

	/**
	 * show Chat Settings
	 */
	public function showChatOptions($by_post = false)
	{
		global $ilCtrl, $ilSetting, $lng, $ilUser;

		$this->__initSubTabs('showChatOptions');

		$this->setHeader();
		
		$form = false;
		if($by_post)
		{
			$form = $this->getChatSettingsForm();
			$form->setValuesByPost();
		}
		else
		{
			$values = array();
			$values['chat_message_notify_status'] = $ilUser->getPref('chat_message_notify_status');
                        $values['chat_message_notify_activate'] = $ilUser->getPref('chat_message_notify_status');

			$values['chat_sound_status'] = $ilUser->getPref('chat_sound_status');
			$values['chat_new_invitation_sound_status'] = $ilUser->getPref('chat_new_invitation_sound_status');
			$values['chat_new_message_sound_status'] = $ilUser->getPref('chat_new_message_sound_status');
			
			$form = $this->getChatSettingsForm();
			$form->setValuesByArray($values);
			
		}
		$this->tpl->setVariable('ADM_CONTENT', $form->getHTML());
		$this->tpl->show();
	}
	
	
	//
	//
	//	PASSWORD FORM
	//
	//

	/**
	* Password form.
	*/
	function showPassword($a_no_init = false)
	{
		global $ilTabs;
		
		$this->__initSubTabs("showPersonalData");
		$ilTabs->activateTab("password");

		$this->setHeader();

		if (!$a_no_init)
		{
			$this->initPasswordForm();
		}
		$this->tpl->setContent($this->form->getHTML());
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
				// check current password
				if (md5($_POST["current_password"]) != $ilUser->getPasswd() and
					$ilUser->getPasswd())
				{
					$error = true;
					$cp->setAlert($this->lng->txt("passwd_wrong"));
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
			if ($this->ilias->getSetting("passwd_auto_generate") != 1 &&
				($ilUser->isPasswordExpired() || $ilUser->isPasswordChangeDemanded()) &&
				($_POST["current_password"] == $_POST["new_password"]))
			{
				$error = true;
				$np->setAlert($this->lng->txt("new_pass_equals_old_pass"));
			}
			
			if (!$error)
			{
				ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);
				$ilUser->resetPassword($_POST["new_password"], $_POST["new_password"]);
				if ($_POST["current_password"] != $_POST["new_password"])
				{
					$ilUser->setLastPasswordChangeToNow();
				}
				$ilCtrl->redirect($this, "showPassword");
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
		
		// session reminder
		if((int)$ilSetting->get('session_handling_type', ilSession::SESSION_HANDLING_FIXED) == ilSession::SESSION_HANDLING_FIXED &&
		   (int)$ilSetting->get('session_reminder_enabled'))
		{
			$cb = new ilCheckboxInputGUI($this->lng->txt('session_reminder'), 'session_reminder_enabled');
			$cb->setInfo($this->lng->txt('session_reminder_info'));
			$cb->setValue(1);
			$cb->setChecked((int)$ilUser->getPref('session_reminder_enabled'));
			
			$expires = ilSession::getSessionExpireValue();			
			$lead_time_gui = new ilTextInputGUI($this->lng->txt('session_reminder_lead_time'), 'session_reminder_lead_time');
			$lead_time_gui->setInfo(sprintf($this->lng->txt('session_reminder_lead_time_info'), ilFormat::_secondsToString($expires, true)));
			$lead_time_gui->setValue($ilUser->getPref('session_reminder_lead_time'));
			$lead_time_gui->setMaxLength(10);
			$lead_time_gui->setSize(10);
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
			global $ilSetting;
			if((int)$ilSetting->get('session_handling_type', ilSession::SESSION_HANDLING_FIXED) == ilSession::SESSION_HANDLING_FIXED &&
			   (int)$ilSetting->get('session_reminder_enabled'))
			{
				$ilUser->setPref('session_reminder_enabled', (int)$_POST['session_reminder_enabled']);
				
				if(!preg_match('/^([0]|([1-9][0-9]*))([\.][0-9][0-9]*)?$/', $_POST['session_reminder_lead_time']))
					$_POST['session_reminder_lead_time'] = 0;
				$ilUser->setPref('session_reminder_lead_time', $_POST['session_reminder_lead_time']);
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
		$tpl->showGeneralSettings(true);
	}
	
	/**
	 * Delete own account dialog - 1st confirmation
	 */
	protected function deleteOwnAccount1()
	{	
		global $ilTabs, $ilUser, $ilToolbar;
		
		$this->setHeader();
		$this->__initSubTabs("deleteOwnAccount");
		$ilTabs->activateTab("delacc");
		
		ilUtil::sendInfo($this->lng->txt('user_delete_own_account_info'));
		$ilToolbar->addButton($this->lng->txt('btn_next'),
			$this->ctrl->getLinkTarget($this, 'deleteOwnAccount2'));
		
		$this->tpl->show();
	}
	
	/**
	 * Delete own account dialog - password confirmation
	 * 
	 * Only available for AUTH_LOCAL
	 */
	protected function deleteOwnAccount2($a_form = null)
	{	
		global $ilTabs;
		
		$this->setHeader();
		$this->__initSubTabs("deleteOwnAccount");
		$ilTabs->activateTab("delacc");
		
		if(!$a_form)
		{
			$a_form = $this->initDeleteAccountPasswordForm();
		}
		
		$this->tpl->setContent($a_form->getHTML());			
		$this->tpl->show();
	}
	
	/**
	 * Init delete own account password form
	 * 
	 * @return ilPropertyFormGUI
	 */
	protected function initDeleteAccountPasswordForm()
	{		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('user_delete_own_account_password_confirmation'));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		$pass = new ilPasswordInputGUI($this->lng->txt("password"), "pwd");
		$pass->setRetype(false);
		$pass->setRequired(true);		
		$form->addItem($pass);
						
		$form->addCommandButton("deleteOwnAccount3", $this->lng->txt("confirm"));
		$form->addCommandButton("showGeneralSettings", $this->lng->txt("cancel"));
		
		return $form;		
	}
	
	/**
	 * Delete own account dialog - final confirmation
	 */
	protected function deleteOwnAccount3()
	{	
		global $ilTabs, $ilUser;
		
		$form = $this->initDeleteAccountPasswordForm();
		if($form->checkInput())
		{			
			if(md5($form->getInput("pwd")) == $ilUser->getPasswd())
			{
				$this->setHeader();
				$this->__initSubTabs("deleteOwnAccount");
				$ilTabs->activateTab("delacc");

				include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
				$cgui = new ilConfirmationGUI();		
				$cgui->setHeaderText($this->lng->txt('user_delete_own_account_final_confirmation'));
				$cgui->setFormAction($this->ctrl->getFormAction($this));
				$cgui->setCancel($this->lng->txt("cancel"), "showGeneralSettings");
				$cgui->setConfirm($this->lng->txt("confirm"), "deleteOwnAccount4");		
				$this->tpl->setContent($cgui->getHTML());			
				$this->tpl->show();
				return;
			}
			
			$input = $form->getItemByPostVar("pwd");
			$input->setAlert($this->lng->txt("passwd_wrong"));
		}
		
		$form->setValuesByPost();
		$this->deleteOwnAccount2($form);
	}
	
	/**
	 * Delete own account dialog - action incl. notification email
	 */
	protected function deleteOwnAccount4()
	{	
		global $ilUser, $ilAuth, $ilSetting, $ilLog;
		
		if(!(bool)$ilSetting->get("user_delete_own_account"))
		{
			$this->ctrl->redirect($this, "showGeneralSettings");
		}
		
		include_once "Services/Mail/classes/class.ilMail.php";
		$mail = new ilMail(ANONYMOUS_USER_ID);
					
		// send mail(s)
		
		$subject = $this->lng->txt("user_delete_own_account_email_subject");			
		$message = $this->lng->txt("user_delete_own_account_email_body");
		
		// salutation/info
		$message = ilMail::getSalutation($ilUser->getId())."\n\n".
			sprintf($message, $ilUser->getLogin(), ILIAS_HTTP_PATH);
		
		// add profile data (see ilAccountRegistrationGUI)
		$message .= "\n\n".$ilUser->getProfileAsString($this->lng);
		
		// signatur
		$message .= ilMail::_getInstallationSignature();
		
		$user_email = $ilUser->getEmail();		
		$admin_mail = $ilSetting->get("user_delete_own_account_email");		
		
		// to user, admin as bcc
		if($user_email)
		{											
			$mail->sendMimeMail($user_email, null, $admin_mail, $subject, $message, null);		
		}
		// admin only
		else if($admin_mail)
		{
			$mail->sendMimeMail($admin_mail, null, null, $subject, $message, null);		
		}
				
		$ilLog->write("Account deleted: ".$ilUser->getLogin()." (".$ilUser->getId().")");
				
		$ilUser->delete();

		// terminate session
		$ilAuth->logout();
		session_destroy();		
		
		ilUtil::redirect("login.php");		 		
	}
}
?>
