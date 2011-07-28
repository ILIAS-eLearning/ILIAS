<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesRegistration Services/Registration
 */

/**
* Class ilAccountRegistrationGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilAccountRegistrationGUI:
*
* @ingroup ServicesRegistration
*/

require_once './Services/Registration/classes/class.ilRegistrationSettings.php';
require_once "./Services/User/classes/class.ilUserAgreement.php";

class ilAccountRegistrationGUI
{
	var $ctrl;
	var $tpl;
	var $profile_incomplete; // ?!

	protected $code_was_used = false;

	public function __construct()
	{
		global $ilCtrl,$tpl,$lng;

		$this->tpl =& $tpl;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,'lang');

		$this->lng =& $lng;
		$this->lng->loadLanguageModule('registration');

		$this->registration_settings = new ilRegistrationSettings();
	}

	public function executeCommand()
	{
		global $ilErr, $tpl;

		if($this->registration_settings->getRegistrationType() == IL_REG_DISABLED)
		{
			$ilErr->raiseError($this->lng->txt('reg_disabled'),$ilErr->FATAL);
		}

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				if($cmd)
				{
					$this->$cmd();
				}
				else
				{
					$this->displayForm();
				}
				break;
		}
		$tpl->show();
		return true;
	}

	public function displayForm()
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.usr_registration.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		$this->tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("registration"));

		// language selection
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_OK",$lng->txt("ok"));
		$this->tpl->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));
		$this->ctrl->getFormAction($this);
		foreach ($lng->getInstalledLanguages() as $lang_key)
		{
			$this->tpl->setCurrentBlock("languages");
			$this->tpl->setVariable("LINK_LANG",$this->ctrl->getLinkTarget($this,'displayForm'));
			$this->tpl->setVariable("LANG_NAME",
							  ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_".$lang_key));
			$this->tpl->setVariable("LANG_ICON", $lang_key);
			$this->tpl->setVariable("BORDER", 0);
			$this->tpl->setVariable("VSPACE", 0);
			$this->tpl->parseCurrentBlock();
		}

		if(!$this->form)
		{
			$this->__initForm();
		}
		$this->tpl->setVariable("FORM", $this->form->getHTML());
	}
	
	protected function __initForm($a_force_code = false)
	{
		global $lng, $ilUser;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		// display code on separate page
		if(($this->registration_settings->registrationCodeRequired() ||
			$this->registration_settings->getAllowCodes()) &&
			(!isset($_POST["usr_registration_code"]) || $a_force_code))
		{
			$sh = new ilFormSectionHeaderGUI();
			$sh->setTitle($lng->txt("prerequisites"));
			$this->form->addItem($sh);
			
			include_once 'Services/Registration/classes/class.ilRegistrationCode.php';
			$code = new ilTextInputGUI($lng->txt("registration_code"), "usr_registration_code");
			$code->setSize(40);
			$code->setMaxLength(ilRegistrationCode::CODE_LENGTH);
			if((bool)$this->registration_settings->registrationCodeRequired())
			{
				$code->setRequired(true);
				$code->setInfo($lng->txt("registration_code_required_info"));
			}
			else
			{
				$code->setInfo($lng->txt("registration_code_optional_info"));
			}
			
			$this->form->addItem($code);	
			
			$this->form->addCommandButton("saveCodeForm", $lng->txt("send"));
		}
		else
		{							
			if(!$this->registration_code)
			{
				$this->registration_code = $_POST["usr_registration_code"];
			}
			
			// code handling, check if role is 
			$predefined_role = null;
			if($this->registration_code)
			{
				$code = new ilNonEditableValueGUI($lng->txt("registration_code"), "usr_registration_code");
				$code->setValue($this->registration_code);
				$this->form->addItem($code);
								
				include_once 'Services/Registration/classes/class.ilRegistrationCode.php';
				$predefined_role = ilRegistrationCode::getCodeRole($this->registration_code);												
			}										
						
			
			// user defined fields

			$user_defined_data = $ilUser->getUserDefinedData();

			include_once './Services/User/classes/class.ilUserDefinedFields.php';
			$user_defined_fields =& ilUserDefinedFields::_getInstance();
			$custom_fields = array();
			foreach($user_defined_fields->getRegistrationDefinitions() as $field_id => $definition)
			{
				if($definition['field_type'] == UDF_TYPE_TEXT)
				{
					$custom_fields["udf_".$definition['field_id']] =
						new ilTextInputGUI($definition['field_name'], "udf_".$definition['field_id']);
					$custom_fields["udf_".$definition['field_id']]->setValue($user_defined_data["f_".$field_id]);
					$custom_fields["udf_".$definition['field_id']]->setMaxLength(255);
					$custom_fields["udf_".$definition['field_id']]->setSize(40);
				}
				else if($definition['field_type'] == UDF_TYPE_WYSIWYG)
				{
					$custom_fields["udf_".$definition['field_id']] =
						new ilTextAreaInputGUI($definition['field_name'], "udf_".$definition['field_id']);
					$custom_fields["udf_".$definition['field_id']]->setValue($user_defined_data["f_".$field_id]);
					$custom_fields["udf_".$definition['field_id']]->setUseRte(true);
				}
				else
				{
					$custom_fields["udf_".$definition['field_id']] =
						new ilSelectInputGUI($definition['field_name'], "udf_".$definition['field_id']);
					$custom_fields["udf_".$definition['field_id']]->setValue($user_defined_data["f_".$field_id]);
					$custom_fields["udf_".$definition['field_id']]->setOptions(
						$user_defined_fields->fieldValuesToSelectArray($definition['field_values']));
				}
				if($definition['required'])
				{
					$custom_fields["udf_".$definition['field_id']]->setRequired(true);
				}
			}

			// standard fields
			include_once("./Services/User/classes/class.ilUserProfile.php");
			$up = new ilUserProfile();
			$up->setMode(ilUserProfile::MODE_REGISTRATION);
			$up->skipGroup("preferences");

			// add fields to form
			$up->addStandardFieldsToForm($this->form, NULL, $custom_fields);
			unset($custom_fields);
						
			// user agreement

			$field = new ilFormSectionHeaderGUI();
			$field->setTitle($lng->txt("usr_agreement"));
			$this->form->addItem($field);

			$field = new ilCustomInputGUI();
			$field->setHTML('<div id="agreement">'.ilUserAgreement::_getText().'</div>');
			$this->form->addItem($field);

			$field = new ilCheckboxInputGUI($lng->txt("accept_usr_agreement"), "usr_agreement");
			$field->setRequired(true);
			$field->setValue(1);
			$this->form->addItem($field);
			
			// remove role select if already defined
			if($predefined_role)
			{
				$this->form->removeItemByPostVar("usr_roles");
								
				$role = new ilHiddenInputGUI("usr_roles");
				$role->setValue($predefined_role);
				$this->form->addItem($role);
			}
			
			$this->form->addCommandButton("saveForm", $lng->txt("register"));
		}	
	}
	
	public function saveCodeForm()
	{		
		global $lng;
		
		$this->__initForm(true);
		if($this->form->checkInput())
		{
			$code = $this->form->getInput("usr_registration_code");
			if($code)
			{
				// invalid code
				include_once './Services/Registration/classes/class.ilRegistrationCode.php';				
				if(!ilRegistrationCode::isUnusedCode($code))
				{
					$code_obj = $this->form->getItemByPostVar('usr_registration_code');
					$code_obj->setAlert($lng->txt('registration_code_not_valid'));
				}
				// valid code
				else
				{
					$this->registration_code = $code;
					unset($this->form);
					return $this->displayForm();					
				}
			}
			// optional code not given
			else
			{
				unset($this->form);
				return $this->displayForm();	
			}
		}
		
		$this->form->setValuesByPost();
		$this->displayForm();
	}

	public function saveForm()
	{
		global $ilias, $lng, $rbacadmin, $ilDB, $ilErr, $ilSetting;

		$this->__initForm();
		$form_valid = $this->form->checkInput();
		
		require_once 'Services/User/classes/class.ilObjUser.php';

		// custom validation

		if(!$this->form->getInput("usr_agreement"))
		{
			$agr_obj = $this->form->getItemByPostVar('usr_agreement');
			$agr_obj->setAlert($lng->txt("force_accept_usr_agreement"));
			$form_valid = false;
		}

		$valid_role = false;

		// manual selection	
		if ($this->registration_settings->roleSelectionEnabled())
		{
			include_once "./Services/AccessControl/classes/class.ilObjRole.php";
			$selected_role = $this->form->getInput("usr_roles");
			if ($selected_role && ilObjRole::_lookupAllowRegister($selected_role))
			{
				$valid_role = true;
			}
		}
		// assign by email
		else
		{				
			include_once 'Services/Registration/classes/class.ilRegistrationEmailRoleAssignments.php';
			$registration_role_assignments = new ilRegistrationRoleAssignments();
			if ($registration_role_assignments->getRoleByEmail($this->form->getInput("usr_email")))
			{
				$valid_role = true;
			}
		}

		// no valid role could be determined
	    if (!$valid_role)
		{
			ilUtil::sendInfo($lng->txt("registration_no_valid_role"));
			$form_valid = false;
		}			

		// validate username
		$login_obj = $this->form->getItemByPostVar('username');
		$login = $this->form->getInput("username");
		if (!ilUtil::isLogin($login))
		{
			$login_obj->setAlert($lng->txt("login_invalid"));
			$form_valid = false;
		}
		else if (ilObjUser::_loginExists($login))
		{
			$login_obj->setAlert($lng->txt("login_exists"));
			$form_valid = false;
		}
		else if ((int)$ilSetting->get('allow_change_loginname') &&
			(int)$ilSetting->get('prevent_reuse_of_loginnames') &&
			ilObjUser::_doesLoginnameExistInHistory($login))
		{
			$login_obj->setAlert($lng->txt('login_exists'));
			$form_valid = false;
		}

		if(!$form_valid)
		{
			ilUtil::sendFailure($lng->txt('form_input_not_valid'));
		}
		else
		{
			$password = $this->__createUser();
			$this->__distributeMails($password);
			$this->login($password);
			return true;
		}		

		$this->form->setValuesByPost();
		$this->displayForm();
		return false;
	}
	
	protected function __createUser()
	{
		global $ilSetting, $rbacadmin;

		$this->userObj = new ilObjUser();
		
		include_once("./Services/User/classes/class.ilUserProfile.php");
		$up = new ilUserProfile();
		$up->setMode(ilUserProfile::MODE_REGISTRATION);

		$map = array();
		$up->skipGroup("preferences");
		$up->skipGroup("settings");
		$up->skipGroup("instant_messengers");
		$up->skipField("password");
		$up->skipField("birthday");
		$up->skipField("upload");
		foreach ($up->getStandardFields() as $k => $v)
		{
			if($v["method"])
			{
				$method = "set".substr($v["method"], 3);
				if(method_exists($this->userObj, $method))
				{
					if ($k != "username")
					{
						$k = "usr_".$k;
					}
					$field_obj = $this->form->getItemByPostVar($k);
					if($field_obj)
					{
						$this->userObj->$method($this->form->getInput($k));
					}
				}
			}
		}

		$this->userObj->setFullName();

		$birthday_obj = $this->form->getItemByPostVar("usr_birthday");
		if ($birthday_obj)
		{
			$birthday = $this->form->getInput("usr_birthday");
			$birthday = $birthday["date"];

			// when birthday was not set, array will not be substituted with string by ilBirthdayInputGui
			if(!is_array($birthday))
			{
				$this->userObj->setBirthday($birthday);
			}
		}

		// messenger
		$map = array("icq", "yahoo", "msn", "aim", "skype", "jabber", "voip");
		foreach($map as $client)
		{
			$field = "usr_im_".$client;
			$field_obj = $this->form->getItemByPostVar($field);
			if($field_obj)
			{
				$this->userObj->setInstantMessengerId($client, $this->form->getInput($field));
			}
		}
		
		$this->userObj->setTitle($this->userObj->getFullname());
		$this->userObj->setDescription($this->userObj->getEmail());

		if ($this->registration_settings->passwordGenerationEnabled())
		{
			$password = ilUtil::generatePasswords(1);
			$password = $password[0];
		}
		else
		{
			$password = $this->form->getInput("usr_password");
		}
		$this->userObj->setPasswd($password);
		
		
		// Set user defined data
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$user_defined_fields =& ilUserDefinedFields::_getInstance();
		$defs = $user_defined_fields->getRegistrationDefinitions();
		$udf = array();
		foreach ($_POST as $k => $v)
		{
			if (substr($k, 0, 4) == "udf_")
			{
				$f = substr($k, 4);
				$udf[$f] = $v;
			}
		}
		$this->userObj->setUserDefinedData($udf);

		$this->userObj->setTimeLimitOwner(7);


		$default_role = false;
		if ($this->registration_settings->roleSelectionEnabled())
		{
			$default_role = $this->form->getInput('usr_roles');
		}
		else
		{
			// Assign by email
			include_once 'Services/Registration/classes/class.ilRegistrationEmailRoleAssignments.php';
			$registration_role_assignments = new ilRegistrationRoleAssignments();
			$default_role = $registration_role_assignments->getRoleByEmail($this->userObj->getEmail());
		}
		
		// get role from code / set code to used
		$code = $this->form->getInput('usr_registration_code');
		$this->code_was_used = false;
		if($this->registration_settings->getRegistrationType() == IL_REG_CODES ||
			($code && $this->registration_settings->getAllowCodes()))
		{
			include_once './Services/Registration/classes/class.ilRegistrationCode.php';
			ilRegistrationCode::useCode($code);
			$this->code_was_used = true;
			
			// #7508: if role is set with code, use that one (even if it overwrites user input)
			$code_role = ilRegistrationCode::getCodeRole($code);
			if($code_role)
			{
				$default_role = $code_role;
			}			
		}
		
		// something went wrong with the form validation
		if(!$default_role)
		{			
			global $ilias;
			$ilias->raiseError("Invalid role selection in registration".
				", IP: ".$_SERVER["REMOTE_ADDR"], $ilias->error_obj->FATAL);
		}

		if ($this->registration_settings->getAccessLimitation())
		{
			include_once 'Services/Registration/classes/class.ilRegistrationRoleAccessLimitations.php';
			$access_limitations_obj = new ilRegistrationRoleAccessLimitations();

			$access_limit_mode = $access_limitations_obj->getMode($default_role);
			if ($access_limit_mode == 'absolute')
			{
				$access_limit = $access_limitations_obj->getAbsolute($default_role);
				$this->userObj->setTimeLimitUnlimited(0);
				$this->userObj->setTimeLimitUntil($access_limit);
			}
			elseif ($access_limit_mode == 'relative')
			{
				$rel_d = (int) $access_limitations_obj->getRelative($default_role,'d');
				$rel_m = (int) $access_limitations_obj->getRelative($default_role,'m');
				$rel_y = (int) $access_limitations_obj->getRelative($default_role,'y');

				$access_limit = $rel_d * 86400 + $rel_m * 2592000 + $rel_y * 31536000 + time();
				$this->userObj->setTimeLimitUnlimited(0);
				$this->userObj->setTimeLimitUntil($access_limit);
			}
			else
			{
				$this->userObj->setTimeLimitUnlimited(1);
				$this->userObj->setTimeLimitUntil(time());
			}
		}
		else
		{
			$this->userObj->setTimeLimitUnlimited(1);
			$this->userObj->setTimeLimitUntil(time());
		}

		$this->userObj->setTimeLimitFrom(time());

		$this->userObj->create();

		
		if($this->registration_settings->getRegistrationType() == IL_REG_DIRECT ||
			$this->registration_settings->getRegistrationType() == IL_REG_CODES ||
			$this->code_was_used)
		{
			$this->userObj->setActive(1,0);
		}
		else if($this->registration_settings->getRegistrationType() == IL_REG_ACTIVATION)
		{
			$this->userObj->setActive(0,0);
		}
		else
		{
			$this->userObj->setActive(0,0);
		}

		$this->userObj->updateOwner();

		// set a timestamp for last_password_change
		// this ts is needed by the ACCOUNT_SECURITY_MODE_CUSTOMIZED
		// in ilSecuritySettings
		$this->userObj->setLastPasswordChangeTS( time() );

		//insert user data in table user_data
		$this->userObj->saveAsNew();

		// store acceptance of user agreement
		$this->userObj->writeAccepted();

		// setup user preferences
		$this->userObj->setLanguage($this->form->getInput('usr_language'));
		$hits_per_page = $ilSetting->get("hits_per_page");
		if ($hits_per_page < 10)
		{
			$hits_per_page = 10;
		}
		$this->userObj->setPref("hits_per_page", $hits_per_page);
		$show_online = $ilSetting->get("show_users_online");
		if ($show_online == "")
		{
			$show_online = "y";
		}
		$this->userObj->setPref("show_users_online", $show_online);
		$this->userObj->writePrefs();

		$rbacadmin->assignUser((int)$default_role, $this->userObj->getId(), true);

		return $password;
	}

	protected function __distributeMails($password)
	{
		global $ilSetting;

		include_once './Services/Language/classes/class.ilLanguage.php';
		include_once './Services/User/classes/class.ilObjUser.php';
		include_once "Services/Mail/classes/class.ilFormatMail.php";
		include_once './Services/Registration/classes/class.ilRegistrationMailNotification.php';

		// Always send mail to approvers
		if($this->registration_settings->getRegistrationType() == IL_REG_APPROVE && !$this->code_was_used)
		{
			$mail = new ilRegistrationMailNotification();
			$mail->setType(ilRegistrationMailNotification::TYPE_NOTIFICATION_CONFIRMATION);
			$mail->setRecipients($this->registration_settings->getApproveRecipients());
			$mail->setAdditionalInformation(array('usr' => $this->userObj));
			$mail->send();
		}
		else
		{
			$mail = new ilRegistrationMailNotification();
			$mail->setType(ilRegistrationMailNotification::TYPE_NOTIFICATION_APPROVERS);
			$mail->setRecipients($this->registration_settings->getApproveRecipients());
			$mail->setAdditionalInformation(array('usr' => $this->userObj));
			$mail->send();
			
		}		
		// Send mail to new user
		
		// Registration with confirmation link ist enabled		
		if($this->registration_settings->getRegistrationType() == IL_REG_ACTIVATION && !$this->code_was_used)
		{			
			include_once 'Services/Mail/classes/class.ilMail.php';
			$mail_obj = new ilMail(ANONYMOUS_USER_ID);			
		
			// mail subject
			$subject = $this->lng->txt("reg_mail_subject_confirmation");

			// mail body			
			$hashcode = ilObjUser::_generateRegistrationHash($this->userObj->getId());
			$body = $this->lng->txt("reg_mail_body_salutation")." ".$this->userObj->getFullname().",\n\n";
			$body .= $this->lng->txt('reg_mail_body_confirmation')."\n".
				ILIAS_HTTP_PATH.'/confirmReg.php?client_id='.CLIENT_ID."&rh=".$hashcode."\n\n";

			$body .= sprintf($this->lng->txt('reg_mail_body_2_confirmation'),
				ilFormat::_secondsToString($this->registration_settings->getRegistrationHashLifetime()))."\n\n";

			$body .= $this->lng->txt('reg_mail_body_3_confirmation');			

			$mail_obj->enableSoap(false);
			$mail_obj->appendInstallationSignature(true);
			$mail_obj->sendMail($this->userObj->getEmail(), '', '',
				$subject,
				$body,
				array(), array('normal'));
		}
		else
		{
			// try individual account mail in user administration
			include_once("Services/Mail/classes/class.ilAccountMail.php");
			include_once './Services/User/classes/class.ilObjUserFolder.php';
			$amail = ilObjUserFolder::_lookupNewAccountMail($GLOBALS["lng"]->getDefaultLanguage());
			if (trim($amail["body"]) != "" && trim($amail["subject"]) != "")
			{				
				$acc_mail = new ilAccountMail();
				$acc_mail->setUser($this->userObj);
				if ($this->registration_settings->passwordGenerationEnabled())
				{
					$acc_mail->setUserPassword($password);
				}
				$acc_mail->send();
			}
			else	// do default mail
			{
				include_once "Services/Mail/classes/class.ilMimeMail.php";
	
				$mmail = new ilMimeMail();
				$mmail->autoCheck(false);
				$mmail->From($ilSetting->get("admin_email"));
				$mmail->To($this->userObj->getEmail());
	
				// mail subject
				$subject = $this->lng->txt("reg_mail_subject");
	
				// mail body
				$body = $this->lng->txt("reg_mail_body_salutation")." ".$this->userObj->getFullname().",\n\n".
					$this->lng->txt("reg_mail_body_text1")."\n\n".
					$this->lng->txt("reg_mail_body_text2")."\n".
					ILIAS_HTTP_PATH."/login.php?client_id=".$ilias->client_id."\n";			
				$body .= $this->lng->txt("login").": ".$this->userObj->getLogin()."\n";
	
				if ($this->registration_settings->passwordGenerationEnabled())
				{
					$body.= $this->lng->txt("passwd").": ".$password."\n";
				}
				$body.= "\n";
	
				// Info about necessary approvement
				if($this->registration_settings->getRegistrationType() == IL_REG_APPROVE && !$this->code_was_used)
				{
					$body .= ($this->lng->txt('reg_mail_body_pwd_generation')."\n\n");
				}			
				
				$body .= ($this->lng->txt("reg_mail_body_text3")."\n\r");
				$body .= $this->userObj->getProfileAsString($this->lng);
				$mmail->Subject($subject);
				$mmail->Body($body);
				$mmail->Send();
			}
		}
	}
	
	public function login($password)
	{
		global $ilias,$lng,$ilLog;

		$ilLog->write("Entered login");

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.usr_registered.html");

		$this->tpl->setVariable("IMG_USER",
			ilUtil::getImagePath("icon_usr_b.gif"));
		$this->tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("registration"));
		$this->tpl->setVariable("TXT_WELCOME", $lng->txt("welcome").", ".$this->userObj->getTitle()."!");

		if (($this->registration_settings->getRegistrationType() == IL_REG_DIRECT or
				$this->registration_settings->getRegistrationType() == IL_REG_CODES or
				$this->code_was_used) and
			!$this->registration_settings->passwordGenerationEnabled())
		{
			$this->tpl->setCurrentBlock("activation");
			$this->tpl->setVariable("TXT_REGISTERED", $lng->txt("txt_registered"));
			$this->tpl->setVariable("FORMACTION", "login.php?cmd=post&target=".$_GET["target"]);
			$this->tpl->setVariable("TARGET","target=\"_parent\"");
			$this->tpl->setVariable("TXT_LOGIN", $lng->txt("login_to_ilias"));
			$this->tpl->setVariable("USERNAME",$this->userObj->getLogin());
			$this->tpl->setVariable("PASSWORD",$password);
			$this->tpl->parseCurrentBlock();
		}
		else if ($this->registration_settings->getRegistrationType() == IL_REG_APPROVE)
		{
			$this->tpl->setVariable("TXT_REGISTERED", $lng->txt("txt_submitted"));
		}
		else if($this->registration_settings->getRegistrationType() == IL_REG_ACTIVATION)
		{
			$this->tpl->setVariable("TXT_REGISTERED", sprintf($lng->txt("reg_confirmation_link_successful"), './login.php'));
			$this->tpl->setVariable("REDIRECT_URL", './login.php');
		}
		else
		{
			$this->tpl->setVariable("TXT_REGISTERED", $lng->txt("txt_registered_passw_gen"));
		}
	}
}
?>
