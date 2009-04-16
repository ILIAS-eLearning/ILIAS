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
include_once("./classes/class.ilObjectGUI.php");
include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
include_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');

/**
* @defgroup ServicesPrivacySecurity Services/PrivacySecurity
*
* @author Stefan Meyer <smeyer@databay.de>
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
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3 => $this->lng->txt('ps_error_message_password_min3_because_chars_numbers_specialchars'),
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
				include_once("./classes/class.ilPermissionGUI.php");
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
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.show_privacy.html','Services/PrivacySecurity');

	 	include_once('Modules/Course/classes/class.ilCourseAgreement.php');
	 	if(ilCourseAgreement::_hasAgreements())
	 	{
			$this->tpl->setCurrentBlock('warning_modify');
			$this->tpl->setVariable('TXT_WARNING',$this->lng->txt('ps_warning_modify'));
			$this->tpl->parseCurrentBlock();
	 	}

	 	$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
	 	$this->tpl->setVariable('TXT_PRIVACY_PROTECTION',$this->lng->txt('ps_privacy_protection'));
	 	$this->tpl->setVariable('TXT_PROFILE_EXPORT',$this->lng->txt('ps_profile_export'));
	 	$this->tpl->setVariable('TXT_EXPORT_COURSE',$this->lng->txt('ps_export_course'));
	 	$this->tpl->setVariable('TXT_EXPORT_CONFIRM',$this->lng->txt('ps_export_confirm'));
	 	$this->tpl->setVariable('TXT_GRP_ACCESS',$this->lng->txt('ps_show_grp_access'));
	 	$this->tpl->setVariable('TXT_CRS_ACCESS',$this->lng->txt('ps_show_crs_access'));

	 	// Check export
	 	$this->tpl->setVariable('CHECK_EXPORT_COURSE',ilUtil::formCheckbox($privacy->enabledExport() ? 1 : 0,'export_course',1));
	 	$this->tpl->setVariable('CHECK_EXPORT_CONFIRM',ilUtil::formCheckbox($privacy->confirmationRequired() ? 1 : 0,'export_confirm',1));
	 	$this->tpl->setVariable('CHECK_GRP_ACCESS',ilUtil::formCheckbox($privacy->enabledGroupAccessTimes() ? 1 : 0,'grp_access_times',1));
	 	$this->tpl->setVariable('CHECK_CRS_ACCESS',ilUtil::formCheckbox($privacy->enabledCourseAccessTimes() ? 1 : 0,'crs_access_times',1));

		// Fora statistics
	 	$this->tpl->setVariable('TXT_STATISTICS',$this->lng->txt('enable_fora_statistics'));
	 	$this->tpl->setVariable('TXT_FORA_STATISTICS',$this->lng->txt('enable_fora_statistics_desc'));
	 	$this->tpl->setVariable('CHECK_FORA_STATISTICS',ilUtil::formCheckbox($privacy->enabledForaStatistics() ? 1 : 0,'fora_statistics',1));

		// Anonymous Fora enabled
	 	$this->tpl->setVariable('TXT_ANONYMITY',$this->lng->txt('disable_anonymous_fora'));
	 	$this->tpl->setVariable('TXT_ANONYMOUS_FORA',$this->lng->txt('disable_anonymous_fora_desc'));
	 	$this->tpl->setVariable('CHECK_ANONYMOUS_FORA',ilUtil::formCheckbox($privacy->disabledAnonymousFora() ? 1 : 0,'anonymous_fora',1));


	 	$this->tpl->setVariable('TXT_SAVE',$this->lng->txt('save'));
	}


	/**
	 * Show Privacy settings
	 *
	 * @access public
	 */
	public function showSecurity()
	{
		global $ilSetting;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$security = ilSecuritySettings::_getInstance();
	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.show_security.html','Services/PrivacySecurity');

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
				$text->setSize(1);
				$text->setMaxLength(2);
			$radio_opt->addSubItem($text);

				$text = new ilTextInputGUI($this->lng->txt('ps_password_max_age'),'password_max_age');
				$text->setInfo($this->lng->txt('ps_password_max_age_info'));
				$text->setValue( $security->getPasswordMaxAge() );
				$text->setSize(1);
				$text->setMaxLength(2);
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
		

		$form->addCommandButton('save_security',$this->lng->txt('save'));
		$this->tpl->setVariable('NEW_FORM',$form->getHTML());
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


		$privacy = ilPrivacySettings::_getInstance();
		$privacy->enableExport((int) $_POST['export_course']);
		$privacy->setConfirmationRequired((int) $_POST['export_confirm']);
		$privacy->enableForaStatistics ((int) $_POST['fora_statistics']);
		$privacy->disableAnonymousFora ((int) $_POST['anonymous_fora']);
		$privacy->showGroupAccessTimes((int) $_POST['grp_access_times']);
		$privacy->showCourseAccessTimes((int) $_POST['crs_access_times']);

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
		    include_once('Modules/Course/classes/class.ilCourseAgreement.php');
		    ilCourseAgreement::_reset();
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
		global $ilErr,$ilAccess, $ilSetting;

		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}


		$security = ilSecuritySettings::_getInstance();

		// auto https detection settings
        $security->setAutomaticHTTPSEnabled((int) $_POST["auto_https_detect_enabled"]);
        $security->setAutomaticHTTPSHeaderName(ilUtil::stripSlashes($_POST["auto_https_detect_header_name"]));
        $security->setAutomaticHTTPSHeaderValue(ilUtil::stripSlashes($_POST["auto_https_detect_header_value"]));

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
