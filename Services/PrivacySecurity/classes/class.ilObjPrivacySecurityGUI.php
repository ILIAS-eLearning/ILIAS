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

		self::initErrorMessages();
	}
	
	public static function initErrorMessages()
	{
		global $lng;
		
		if(is_array(self::$ERROR_MESSAGE))
		{
			return;
		}
		
		$lng->loadLanguageModule('ps');

		ilObjPrivacySecurityGUI::$ERROR_MESSAGE = array (
		   ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_AUTO_HTTPS => $lng->txt("ps_error_message_https_header_missing"),
		   ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE => $lng->txt('https_not_possible'),
	       ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTP_NOT_AVAILABLE => $lng->txt('http_not_possible'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MIN_LENGTH => $lng->txt('ps_error_message_invalid_password_min_length'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_LENGTH => $lng->txt('ps_error_message_invalid_password_max_length'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_AGE => $lng->txt('ps_error_message_invalid_password_max_age'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_INVALID_LOGIN_MAX_ATTEMPTS => $lng->txt('ps_error_message_invalid_login_max_attempts'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN1 => $lng->txt('ps_error_message_password_min1_because_chars'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN2 => $lng->txt('ps_error_message_password_min2_because_chars_numbers'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3 => $lng->txt('ps_error_message_password_min3_because_chars_numbers_sc'),
	       ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH => $lng->txt('ps_error_message_password_max_less_min')
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
			ilUtil::sendInfo($this->lng->txt('ps_warning_modify'));
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
		
		include_once "Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php";
		ilAdministrationSettingsFormHandler::addFieldsToForm(
			ilAdministrationSettingsFormHandler::FORM_PRIVACY, 
			$form,
			$this
		);

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
		
		include_once "Services/Administration/classes/class.ilAdministrationSettingsFormHandler.php";
		ilAdministrationSettingsFormHandler::addFieldsToForm(
			ilAdministrationSettingsFormHandler::FORM_SECURITY, 
			$form,
			$this
		);

		// $form->addCommandButton('save_security',$this->lng->txt('save'));
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
		
		// to determine if agreements need to be reset - see below
		$old_settings = array(
			'export_course' => $privacy->enabledCourseExport(),
			'export_group' => $privacy->enabledGroupExport(),			
			'export_confirm_course' => $privacy->courseConfirmationRequired(),
			'export_confirm_group' => $privacy->groupConfirmationRequired(),
			'crs_access_times' => $privacy->enabledCourseAccessTimes(),
			'grp_access_times' => $privacy->enabledGroupAccessTimes()
		);				
	
		$privacy->enableCourseExport((int) in_array('export_course', $_POST['profile_protection']));
		$privacy->enableGroupExport((int) in_array('export_group', $_POST['profile_protection']));
		$privacy->setCourseConfirmationRequired((int) in_array('export_confirm_course', $_POST['profile_protection']));
		$privacy->setGroupConfirmationRequired((int) in_array('export_confirm_group', $_POST['profile_protection']));
		$privacy->showGroupAccessTimes((int) in_array('grp_access_times', $_POST['profile_protection']));
		$privacy->showCourseAccessTimes((int) in_array('crs_access_times', $_POST['profile_protection']));
		
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
			
			// reset agreements?
			$do_reset = false;
			if(!$old_settings['export_course'] && $privacy->enabledCourseExport())
			{
				$do_reset = true;
			}
			if(!$do_reset && !$old_settings['export_group'] && $privacy->enabledGroupExport())
			{
				$do_reset = true;
			}
			if(!$do_reset && !$old_settings['export_confirm_course'] && $privacy->courseConfirmationRequired())
			{
				$do_reset = true;
			}
			if(!$do_reset && !$old_settings['export_confirm_group'] && $privacy->groupConfirmationRequired())
			{
				$do_reset = true;
			}
			if(!$do_reset && !$old_settings['crs_access_times'] && $privacy->enabledCourseAccessTimes())
			{
				$do_reset = true;
			}
			if(!$do_reset && !$old_settings['grp_access_times'] && $privacy->enabledGroupAccessTimes())
			{
				$do_reset = true;
			}
			if($do_reset)
			{
				include_once('Services/Membership/classes/class.ilMemberAgreement.php');
				ilMemberAgreement::_reset();				
			}
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

		/*
		$security = ilSecuritySettings::_getInstance();

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
		*/
		
		$this->showSecurity();
	}


	/**
     * return error message for error code
     *
     * @param int $code
     * @return string
     */

	public static function getErrorMessage ($code) 
	{
		self::initErrorMessages();
        return ilObjPrivacySecurityGUI::$ERROR_MESSAGE[$code];
	}
	
	public function addToExternalSettingsForm($a_form_id)
	{						
		switch($a_form_id)
		{			
			case ilAdministrationSettingsFormHandler::FORM_COURSE:
				
				$privacy = ilPrivacySettings::_getInstance();
				
				$subitems = array(
					'ps_export_course' => array($privacy->enabledCourseExport(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'ps_export_confirm' => array($privacy->courseConfirmationRequired(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'ps_show_crs_access' => array($privacy->enabledCourseAccessTimes(), ilAdministrationSettingsFormHandler::VALUE_BOOL)					
				);
				$fields = array(
					'ps_profile_export' => array(null, null, $subitems)				
				);										
				return array(array("showPrivacy", $fields));	
				
			case ilAdministrationSettingsFormHandler::FORM_GROUP:
								
				$privacy = ilPrivacySettings::_getInstance();
				
				$subitems = array(
					'ps_export_groups' => array($privacy->enabledGroupExport(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'ps_export_confirm_group' => array($privacy->groupConfirmationRequired(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
					'ps_show_grp_access' => array($privacy->enabledGroupAccessTimes(), ilAdministrationSettingsFormHandler::VALUE_BOOL)
				);
				$fields = array(
					'ps_profile_export' => array(null, null, $subitems)									
				);										
				return array(array("showPrivacy", $fields));	
		}
	}
}

?>