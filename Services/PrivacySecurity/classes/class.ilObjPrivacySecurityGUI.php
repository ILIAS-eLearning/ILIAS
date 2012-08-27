<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");
include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');

/**
* @defgroup ServicesPrivacySecurity Services/PrivacySecurity
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls ilObjPrivacySecurityGUI: ilPermissionGUI
*
* @ingroup ServicesPrivacySecurity
*/
class ilObjPrivacySecurityGUI extends ilObjectGUI
{
    private static $ERROR_MESSAGE;
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = 'ps';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('ps');

		ilObjPrivacySecurityGUI::$ERROR_MESSAGE = array (
		   ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_AUTO_HTTPS => $this->lng->txt("ps_error_message_https_header_missing"),
		   ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE => $this->lng->txt('https_not_possible'),
	       ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTP_NOT_AVAILABLE => $this->lng->txt('http_not_possible'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MIN_LENGTH => $this->lng->txt('ps_error_message_invalid_password_min_length'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_LENGTH => $this->lng->txt('ps_error_message_invalid_password_max_length'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_AGE => $this->lng->txt('ps_error_message_invalid_password_max_age'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_LOGIN_MAX_ATTEMPTS => $this->lng->txt('ps_error_message_invalid_login_max_attempts'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN2 => $this->lng->txt('ps_error_message_password_min2_because_chars_numbers'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3 => $this->lng->txt('ps_error_message_password_min3_because_chars_numbers_sc'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH => $this->lng->txt('ps_error_message_password_max_less_min')
		);
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem,$ilErr,$ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
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
					$cmd = "showPrivacy";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("show_privacy",
				$this->ctrl->getLinkTarget($this, "showPrivacy"),
				'showPrivacy');
            $this->tabs_gui->addTarget("show_security",
				$this->ctrl->getLinkTarget($this, "showSecurity"),
				'showSecurity');

		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	/**
	 * Show Privacy settings
	 *
	 * @access public
	 */
	public function showPrivacy()
	{
		$privacy = ilPrivacySettings::_getInstance();

		$this->tabs_gui->setTabActive('show_privacy');

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('ps_privacy_protection'));
		
	 	include_once('Services/Membership/classes/class.ilMemberAgreement.php');
	 	if(ilMemberAgreement::_hasAgreements())
	 	{
			$html = new ilNonEditableValueGUI();
			$html->setValue($this->lng->txt('ps_warning_modify'));
			$form->addItem($html);
	 	}

		$value = array();
		if($privacy->enabledCourseExport())
		{
			$value[] = "export_course";
		}
		if($privacy->enabledGroupExport())
		{
			$value[] = "export_group";
		}
		if($privacy->courseConfirmationRequired())
		{
			$value[] = "export_confirm_course";
		}
		if($privacy->groupConfirmationRequired())
		{
			$value[] = "export_confirm_group";
		}
		if($privacy->enabledGroupAccessTimes())
		{
			$value[] = "grp_access_times";
		}
		if($privacy->enabledCourseAccessTimes())
		{
			$value[] = "crs_access_times";
		}
		$group = new ilCheckboxGroupInputGUI($this->lng->txt('ps_profile_export'),'profile_protection');
		$group->setValue($value);
		$check = new ilCheckboxOption();
		$check->setTitle($this->lng->txt('ps_export_course'));
		$check->setValue('export_course');
		$group->addOption($check);
		$check = new ilCheckboxOption();
		$check->setTitle($this->lng->txt('ps_export_groups'));
		$check->setValue('export_group');
		$group->addOption($check);
		$check = new ilCheckboxOption();
		$check->setTitle($this->lng->txt('ps_export_confirm'));
		$check->setValue('export_confirm_course');
		$group->addOption($check);
		$check = new ilCheckboxOption();
		$check->setTitle($this->lng->txt('ps_export_confirm_group'));
		$check->setValue('export_confirm_group');
		$group->addOption($check);
		$check = new ilCheckboxOption();
		$check->setTitle($this->lng->txt('ps_show_grp_access'));
		$check->setValue('grp_access_times');
		$group->addOption($check);
		$check = new ilCheckboxOption();
		$check->setTitle($this->lng->txt('ps_show_crs_access'));
		$check->setValue('crs_access_times');
		$group->addOption($check);
		$form->addItem($group);

		$check = new ilCheckboxInputGui($this->lng->txt('enable_fora_statistics'), 'fora_statistics');
		$check->setInfo($this->lng->txt('enable_fora_statistics_desc'));
		$check->setChecked($privacy->enabledForaStatistics());
		$form->addItem($check);

		$check = new ilCheckboxInputGui($this->lng->txt('enable_anonymous_fora'), 'anonymous_fora');
		$check->setInfo($this->lng->txt('enable_anonymous_fora_desc'));
		$check->setChecked($privacy->enabledAnonymousFora());
		$form->addItem($check);

		$check = new ilCheckboxInputGui($this->lng->txt('enable_sahs_protocol_data'), 'enable_sahs_pd');
		$check->setInfo($this->lng->txt('enable_sahs_protocol_data_desc'));
		$check->setChecked($privacy->enabledSahsProtocolData());
		$form->addItem($check);

		$check = new ilCheckboxInputGui($this->lng->txt('rbac_log'), 'rbac_log');
		$check->setInfo($this->lng->txt('rbac_log_info'));
		$check->setChecked($privacy->enabledRbacLog());
		$form->addItem($check);

		$age = new ilNumberInputGUI($this->lng->txt('rbac_log_age'),'rbac_log_age');
		$age->setInfo($this->lng->txt('rbac_log_age_info'));
	    $age->setValue($privacy->getRbacLogAge());
		$age->setMinValue(1);
		$age->setMaxValue(24);
		$age->setSize(2);
		$age->setMaxLength(2);
		$check->addSubItem($age);

		$form->addCommandButton('save_privacy',$this->lng->txt('save'));
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * Show Privacy settings
	 *
	 * @access public
	 */
	public function showSecurity()
	{
		global $ilSetting, $ilUser, $rbacreview;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$security = ilSecuritySettings::_getInstance();
	 	
		$this->tabs_gui->setTabActive('show_security');

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('ps_security_protection'));

		// Form checkbox
		$check = new ilCheckboxInputGUI($this->lng->txt('ps_auto_https'),'auto_https_detect_enabled');
		$check->setOptionTitle($this->lng->txt('ps_auto_https_description'));
		$check->setChecked($security->isAutomaticHTTPSEnabled() ? 1 : 0);
		$check->setValue(1);

			$text = new ilTextInputGUI($this->lng->txt('ps_auto_https_header_name'),'auto_https_detect_header_name');
			$text->setValue($security->getAutomaticHTTPSHeaderName());
			$text->setSize(24);
			$text->setMaxLength(64);
		$check->addSubItem($text);

			$text = new ilTextInputGUI($this->lng->txt('ps_auto_https_header_value'),'auto_https_detect_header_value');
			$text->setValue($security->getAutomaticHTTPSHeaderValue());
			$text->setSize(24);
			$text->setMaxLength(64);
		$check->addSubItem($text);

		$form->addItem($check);

		$check2 = new ilCheckboxInputGUI($this->lng->txt('activate_https'),'https_enabled');
		$check2->setChecked($security->isHTTPSEnabled() ? 1 : 0);
		$check2->setValue(1);
		$form->addItem($check2);

		$radio_group = new ilRadioGroupInputGUI($this->lng->txt('ps_account_security_mode'), 'account_security_mode' );
		$radio_group->setValue($security->getAccountSecurityMode());

			$radio_opt = new ilRadioOption($this->lng->txt('ps_account_security_mode_default'),ilSecuritySettings::ACCOUNT_SECURITY_MODE_DEFAULT);
		$radio_group->addOption($radio_opt);

			$radio_opt = new ilRadioOption($this->lng->txt('ps_account_security_mode_customized'),ilSecuritySettings::ACCOUNT_SECURITY_MODE_CUSTOMIZED);

				$check = new ilCheckboxInputGUI($this->lng->txt('ps_password_chars_and_numbers_enabled'),'password_chars_and_numbers_enabled');
				$check->setChecked( $security->isPasswordCharsAndNumbersEnabled() ? 1 : 0 );
				//$check->setOptionTitle($this->lng->txt('ps_password_chars_and_numbers_enabled'));
				$check->setInfo($this->lng->txt('ps_password_chars_and_numbers_enabled_info'));
			$radio_opt->addSubItem($check);

				$check = new ilCheckboxInputGUI($this->lng->txt('ps_password_special_chars_enabled'),'password_special_chars_enabled');
				$check->setChecked( $security->isPasswordSpecialCharsEnabled() ? 1 : 0 );
				//$check->setOptionTitle($this->lng->txt('ps_password_special_chars_enabled'));
				$check->setInfo($this->lng->txt('ps_password_special_chars_enabled_info'));
			$radio_opt->addSubItem($check);

				$text = new ilTextInputGUI($this->lng->txt('ps_password_min_length'),'password_min_length');
				$text->setInfo($this->lng->txt('ps_password_min_length_info'));
				$text->setValue( $security->getPasswordMinLength() );
				$text->setSize(1);
				$text->setMaxLength(2);
			$radio_opt->addSubItem($text);

				$text = new ilTextInputGUI($this->lng->txt('ps_password_max_length'),'password_max_length');
				$text->setInfo($this->lng->txt('ps_password_max_length_info'));
				$text->setValue( $security->getPasswordMaxLength() );
				$text->setSize(2);
				$text->setMaxLength(3);
			$radio_opt->addSubItem($text);

				$text = new ilTextInputGUI($this->lng->txt('ps_password_max_age'),'password_max_age');
				$text->setInfo($this->lng->txt('ps_password_max_age_info'));
				$text->setValue( $security->getPasswordMaxAge() );
				$text->setSize(2);
				$text->setMaxLength(3);
			$radio_opt->addSubItem($text);

				$text = new ilTextInputGUI($this->lng->txt('ps_login_max_attempts'),'login_max_attempts');
				$text->setInfo($this->lng->txt('ps_login_max_attempts_info'));
				$text->setValue( $security->getLoginMaxAttempts() );
				$text->setSize(1);
				$text->setMaxLength(2);
			$radio_opt->addSubItem($text);

		$radio_group->addOption($radio_opt);
		$form->addItem($radio_group);

		$check = new ilCheckboxInputGUI($this->lng->txt('ps_password_change_on_first_login_enabled'),'password_change_on_first_login_enabled');
		$check->setInfo($this->lng->txt('ps_password_change_on_first_login_enabled_info'));
		$check->setChecked( $security->isPasswordChangeOnFirstLoginEnabled() ? 1 : 0 );
		$form->addItem($check);

		// file suffix replacement
		$ti = new ilTextInputGUI($this->lng->txt("file_suffix_repl"), "suffix_repl_additional");
		$ti->setMaxLength(200);
		$ti->setSize(40);
		$ti->setInfo($this->lng->txt("file_suffix_repl_info")." ".SUFFIX_REPL_DEFAULT);
		$ti->setValue($ilSetting->get("suffix_repl_additional"));
		$form->addItem($ti);
		
		// prevent login from multiple pcs at the same time
		$objCb = new ilCheckboxInputGUI($this->lng->txt('ps_prevent_simultaneous_logins'), 'ps_prevent_simultaneous_logins');
		$objCb->setChecked((int)$security->isPreventionOfSimultaneousLoginsEnabled());
		$objCb->setValue(1);
		$objCb->setOptionTitle($this->lng->txt('ps_prevent_simultaneous_logins_info'));
		$form->addItem($objCb);
		
		// protected admin
		$admin = new ilCheckboxInputGUI($GLOBALS['lng']->txt('adm_adm_role_protect'),'admin_role');
		$admin->setDisabled(!$rbacreview->isAssigned($ilUser->getId(),SYSTEM_ROLE_ID));
		$admin->setInfo($GLOBALS['lng']->txt('adm_adm_role_protect_info'));
		$admin->setChecked((int) $security->isAdminRoleProtected());
		$admin->setValue(1);
		$form->addItem($admin);

		$form->addCommandButton('save_security',$this->lng->txt('save'));
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * Save privacy settings
	 *
	 * @access public
	 *
	 */
	public function save_privacy()
	{
		global $ilErr,$ilAccess, $ilSetting;

		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}

		if((int) $_POST['rbac_log_age'] > 24)
		{
			$_POST['rbac_log_age'] = 24;
		}
		else if((int) $_POST['rbac_log_age'] < 1)
		{
			$_POST['rbac_log_age'] = 1;
		}

		$_POST['profile_protection'] = isset($_POST['profile_protection']) ? $_POST['profile_protection'] : array();

		$privacy = ilPrivacySettings::_getInstance();
		$privacy->enableCourseExport((int) in_array('export_course', $_POST['profile_protection']));
		$privacy->enableGroupExport((int) in_array('export_group', $_POST['profile_protection']));
		$privacy->setCourseConfirmationRequired((int) in_array('export_confirm_course', $_POST['profile_protection']));
		$privacy->setGroupConfirmationRequired((int) in_array('export_confirm_group', $_POST['profile_protection']));
		$privacy->showGroupAccessTimes((int) in_array('grp_access_times', $_POST['profile_protection']));
		$privacy->showCourseAccessTimes((int) in_array('crs_access_times', $_POST['profile_protection']));
		$privacy->enableForaStatistics ((int) $_POST['fora_statistics']);
		$privacy->enableAnonymousFora ((int) $_POST['anonymous_fora']);
		$privacy->enableRbacLog((int) $_POST['rbac_log']);
		$privacy->setRbacLogAge((int) $_POST['rbac_log_age']);
		$privacy->enableSahsProtocolData((int) $_POST['enable_sahs_pd']);
		
        // validate settings
        $code = $privacy->validate();

        // if error code != 0, display error and do not save
        if ($code != 0)
        {
            $msg = $this->getErrorMessage ($code);
            ilUtil::sendFailure($msg);
        }
        else
        {
            $privacy->save();
		    include_once('Services/Membership/classes/class.ilMemberAgreement.php');
		    ilMemberAgreement::_reset();
		    ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        }

		$this->showPrivacy();
	}

	/**
	 * Save security settings
	 *
	 * @access public
	 *
	 */
	public function save_security()
	{
		global $ilErr,$ilAccess, $ilSetting, $rbacreview, $ilUser;

		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}


		$security = ilSecuritySettings::_getInstance();

		// auto https detection settings
        $security->setAutomaticHTTPSEnabled((int) $_POST["auto_https_detect_enabled"]);
        $security->setAutomaticHTTPSHeaderName(ilUtil::stripSlashes($_POST["auto_https_detect_header_name"]));
        $security->setAutomaticHTTPSHeaderValue(ilUtil::stripSlashes($_POST["auto_https_detect_header_value"]));
        
         // prevention of simultaneous logins with the same account
        $security->setPreventionOfSimultaneousLogins((bool)$_POST['ps_prevent_simultaneous_logins']);

        // ilias https handling settings
        $security->setHTTPSEnabled($_POST["https_enabled"]);

		// account security settings
		$security->setAccountSecurityMode((int) $_POST["account_security_mode"]);
		$security->setPasswordCharsAndNumbersEnabled((bool) $_POST["password_chars_and_numbers_enabled"]);
		$security->setPasswordSpecialCharsEnabled((bool) $_POST["password_special_chars_enabled"]);
		$security->setPasswordMinLength((int) $_POST["password_min_length"]);
		$security->setPasswordMaxLength((int) $_POST["password_max_length"]);
		$security->setPasswordMaxAge((int) $_POST["password_max_age"]);
		$security->setLoginMaxAttempts((int) $_POST["login_max_attempts"]);

		// change password on first login settings
		$security->setPasswordChangeOnFirstLoginEnabled((bool) $_POST['password_change_on_first_login_enabled']);

		// file suffic replacements
		$ilSetting->set("suffix_repl_additional", $_POST["suffix_repl_additional"]);

        // validate settings
		if($rbacreview->isAssigned($ilUser->getId(),SYSTEM_ROLE_ID))
		{
			$security->protectedAdminRole((int) $_POST['admin_role']);
		}
		
        $code = $security->validate();

        // if error code != 0, display error and do not save
        if ($code != 0)
        {
            $msg = $this->getErrorMessage ($code);
            ilUtil::sendFailure($msg);
        } else
        {
            $security->save();
		    ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        }

		$this->showSecurity();
	}


	/**
     * return error message for error code
     *
     * @param int $code
     * @return string
     */

	private static function getErrorMessage ($code) {
        return ilObjPrivacySecurityGUI::$ERROR_MESSAGE[$code];
	}
}
?>
