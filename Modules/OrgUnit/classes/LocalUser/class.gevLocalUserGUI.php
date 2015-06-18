<?php
require_once "Services/User/classes/class.ilObjUserGUI.php";
/**
* Class gevLocalUserGUI
*
* @author Stefan Hecken <meyer@leifos.com>
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id: class.ilObjUserGUI.php 57724 2015-02-02 08:43:40Z rklees $
*
* @ilCtrl_Calls gevLocalUserGUI: ilLearningProgressGUI, ilObjectOwnershipManagementGUI
*
* @ingroup ServicesUser
*/
class gevLocalUserGUI extends ilObjUserGUI {
	static $default_role = "Agt-ID";

	public function __construct($a_data,$a_id,$a_call_by_reference = false, $a_prepare_output = true) {
		parent::__construct($a_data,$a_id,$a_call_by_reference, $a_prepare_output);
	}


	function initForm($a_mode)
	{
		global $lng, $ilCtrl, $styleDefinition, $ilSetting, $ilClientIniFile, $ilUser;

		$settings = $ilSetting->getAll();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));
		if ($a_mode == "create")
		{
			$this->form_gui->setTitle($lng->txt("usr_new"));
		}
		else
		{
			$this->form_gui->setTitle($lng->txt("usr_edit"));
		}

		// login data
		$sec_l = new ilFormSectionHeaderGUI();
		$sec_l->setTitle($lng->txt("login_data"));
		$this->form_gui->addItem($sec_l);

		// authentication mode
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
		$active_auth_modes = ilAuthUtils::_getActiveAuthModes();
		$am = new ilSelectInputGUI($lng->txt("auth_mode"), "auth_mode");
		$option = array();
		foreach ($active_auth_modes as $auth_name => $auth_key)
		{
			if ($auth_name == 'default')
			{
				$name = $this->lng->txt('auth_'.$auth_name)." (".$this->lng->txt('auth_'.ilAuthUtils::_getAuthModeName($auth_key)).")";
			}
			else
			{
				$name = $this->lng->txt('auth_'.$auth_name);
			}
			$option[$auth_name] = $name;
		}
		$am->setOptions($option);
		$this->form_gui->addItem($am);

		// login
		$lo = new ilUserLoginInputGUI($lng->txt("login"), "login");
		$lo->setRequired(true);
		if ($a_mode == "edit")
		{
			$lo->setCurrentUserId($this->object->getId());
			try
			{
				include_once 'Services/Calendar/classes/class.ilDate.php';				
 
				$last_history_entry = ilObjUser::_getLastHistoryDataByUserId($this->object->getId());				
				$lo->setInfo(
					sprintf(
						$this->lng->txt('usr_loginname_history_info'),
						ilDatePresentation::formatDate(new ilDateTime($last_history_entry[1], IL_CAL_UNIX)),
						$last_history_entry[0]
					)
				);		
			}
			catch(ilUserException $e) { }
		}
		
		$this->form_gui->addItem($lo);

		// passwords
// @todo: do not show passwords, if there is not a single auth, that
// allows password setting
		{
			$pw = new ilPasswordInputGUI($lng->txt("passwd"), "passwd");
			$pw->setSize(32);
			$pw->setMaxLength(32);
			$pw->setValidateAuthPost("auth_mode");
			if ($a_mode == "create")
			{
				$pw->setRequiredOnAuth(true);
			}
			$pw->setInfo(ilUtil::getPasswordRequirementsInfo());
			$this->form_gui->addItem($pw);
		}
		// @todo: invisible/hidden passwords

		// external account
		include_once('./Services/Authentication/classes/class.ilAuthUtils.php');
		if(ilAuthUtils::_isExternalAccountEnabled())
		{
			$ext = new ilTextInputGUI($lng->txt("user_ext_account"), "ext_account");
			$ext->setSize(40);
			$ext->setMaxLength(50);
			$ext->setInfo($lng->txt("user_ext_account_desc"));
			$this->form_gui->addItem($ext);
		}

		// login data
		$sec_si = new ilFormSectionHeaderGUI();
		$sec_si->setTitle($this->lng->txt("system_information"));
		$this->form_gui->addItem($sec_si);

		// create date, approve date, agreement date, last login
		if ($a_mode == "edit")
		{
			$sia = array("create_date", "approve_date", "agree_date", "last_login", "owner");
			foreach($sia as $a)
			{
				$siai = new ilNonEditableValueGUI($lng->txt($a), $a);
				$this->form_gui->addItem($siai);
			}
		}

		// active
		/*$ac = new ilCheckboxInputGUI($lng->txt("active"), "active");
		$ac->setChecked(true);
		// gev-patch start
		$ac->setInfo("Achtung: Gesperrte Benutzerkonten dürfen ausschließlich"
					." mit einer schriftlichen Einwilligungserklärung auf aktiv"
					." gesetzt werden. Einwilligungserklärung zwingend archivieren.");
		// gev-patch end
		$this->form_gui->addItem($ac);*/

		$active = new ilHiddenInputGUI("active");
		$active->setValue(1);
		$this->form_gui->addItem($active);

		// access	@todo: get fields right (names change)
		$lng->loadLanguageModule('crs');
		
		// access
		$radg = new ilRadioGroupInputGUI($lng->txt("time_limit"), "time_limit_unlimited");
		$radg->setValue(1);
			$op1 = new ilRadioOption($lng->txt("user_access_unlimited"), 1);
			$radg->addOption($op1);
			$op2 = new ilRadioOption($lng->txt("user_access_limited"), 0);
			$radg->addOption($op2);
		
//		$ac = new ilCheckboxInputGUI($lng->txt("time_limit"), "time_limit_unlimited");
//		$ac->setChecked(true);
//		$ac->setOptionTitle($lng->txt("crs_unlimited"));

		// access.from
		$acfrom = new ilDateTimeInputGUI($this->lng->txt("crs_from"), "time_limit_from");
		$acfrom->setShowTime(true);
//		$ac->addSubItem($acfrom);
		$op2->addSubItem($acfrom);

		// access.to
		$acto = new ilDateTimeInputGUI($this->lng->txt("crs_to"), "time_limit_until");
		$acto->setShowTime(true);
//		$ac->addSubItem($acto);
		$op2->addSubItem($acto);

//		$this->form_gui->addItem($ac);
		$this->form_gui->addItem($radg);

		require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		if (ilDiskQuotaActivationChecker::_isActive())
		{
			$lng->loadLanguageModule("file");
			
			$quota_head = new ilFormSectionHeaderGUI();
			$quota_head->setTitle($lng->txt("repository_disk_quota"));
			$this->form_gui->addItem($quota_head);
			
			// disk quota
			$disk_quota = new ilTextInputGUI($lng->txt("disk_quota"), "disk_quota");
			$disk_quota->setSize(10);
			$disk_quota->setMaxLength(11);
			$disk_quota->setInfo($this->lng->txt("enter_in_mb_desc"));
			$this->form_gui->addItem($disk_quota);

			if ($a_mode == "edit")
			{
				// show which disk quota is in effect, and explain why
				require_once 'Services/WebDAV/classes/class.ilDiskQuotaChecker.php';
				$dq_info = ilDiskQuotaChecker::_lookupDiskQuota($this->object->getId());
				if ($dq_info['user_disk_quota'] > $dq_info['role_disk_quota'])
				{
					$info_text = sprintf($lng->txt('disk_quota_is_1_instead_of_2_by_3'),
						ilFormat::formatSize($dq_info['user_disk_quota'],'short'),
						ilFormat::formatSize($dq_info['role_disk_quota'],'short'),
						$dq_info['role_title']);
				}
				else if (is_infinite($dq_info['role_disk_quota']))
				{
					$info_text = sprintf($lng->txt('disk_quota_is_unlimited_by_1'), $dq_info['role_title']);
				}
				else
				{
					$info_text = sprintf($lng->txt('disk_quota_is_1_by_2'),
						ilFormat::formatSize($dq_info['role_disk_quota'],'short'),
						$dq_info['role_title']);
				}
				$disk_quota->setInfo($this->lng->txt("enter_in_mb_desc").'<br>'.$info_text);


				// disk usage
				$du_info = ilDiskQuotaChecker::_lookupDiskUsage($this->object->getId());
				$disk_usage = new ilNonEditableValueGUI($lng->txt("disk_usage"), "disk_usage");
				if ($du_info['last_update'] === null)
				{
					$disk_usage->setValue($lng->txt('unknown'));
				}
				else
				{
			        require_once './Services/Utilities/classes/class.ilFormat.php';
					$disk_usage->setValue(ilFormat::formatSize($du_info['disk_usage'],'short'));
				$info = '<table class="il_user_quota_disk_usage_overview">';
					// write the count and size of each object type
					foreach ($du_info['details'] as $detail_data)
					{
						$info .= '<tr>'.
							'<td class="std">'.$detail_data['count'].'</td>'.
							'<td class="std">'.$lng->txt($detail_data['type']).'</td>'.
							'<td class="std">'.ilFormat::formatSize($detail_data['size'], 'short').'</td>'.
							'</tr>'
							;
					}
					$info .= '</table>';
					$info .= '<br>'.$this->lng->txt('last_update').': '.
						ilDatePresentation::formatDate(new ilDateTime($du_info['last_update'], IL_CAL_DATETIME));
					$disk_usage->setInfo($info);

				}
				$this->form_gui->addItem($disk_usage);

				// date when the last disk quota reminder was sent to the user
				if (true || $dq_info['last_reminder'])
				{
					$reminder = new ilNonEditableValueGUI($lng->txt("disk_quota_last_reminder_sent"), "last_reminder");
					$reminder->setValue(
						ilDatePresentation::formatDate(new ilDateTime($dq_info['last_reminder'], IL_CAL_DATETIME))
					);
					$reminder->setInfo($this->lng->txt("disk_quota_last_reminder_sent_desc"));
					$this->form_gui->addItem($reminder);
				}
			}
		}
		
		if (ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
		{
			$lng->loadLanguageModule("file");
		
			$quota_head = new ilFormSectionHeaderGUI();
			$quota_head->setTitle($lng->txt("personal_workspace_disk_quota"));
			$this->form_gui->addItem($quota_head);
			
			// personal workspace disk quota
			$wsp_disk_quota = new ilTextInputGUI($lng->txt("disk_quota"), "wsp_disk_quota");
			$wsp_disk_quota->setSize(10);
			$wsp_disk_quota->setMaxLength(11);
			$wsp_disk_quota->setInfo($this->lng->txt("enter_in_mb_desc"));
			$this->form_gui->addItem($wsp_disk_quota);
			
			if ($a_mode == "edit")
			{
				// show which disk quota is in effect, and explain why
				require_once 'Services/WebDAV/classes/class.ilDiskQuotaChecker.php';
				$dq_info = ilDiskQuotaChecker::_lookupPersonalWorkspaceDiskQuota($this->object->getId());
				if ($dq_info['user_wsp_disk_quota'] > $dq_info['role_wsp_disk_quota'])
				{
					$info_text = sprintf($lng->txt('disk_quota_is_1_instead_of_2_by_3'),
						ilFormat::formatSize($dq_info['user_wsp_disk_quota'],'short'),
						ilFormat::formatSize($dq_info['role_wsp_disk_quota'],'short'),
						$dq_info['role_title']);
				}
				else if (is_infinite($dq_info['role_wsp_disk_quota']))
				{
					$info_text = sprintf($lng->txt('disk_quota_is_unlimited_by_1'), $dq_info['role_title']);
				}
				else
				{
					$info_text = sprintf($lng->txt('disk_quota_is_1_by_2'),
						ilFormat::formatSize($dq_info['role_wsp_disk_quota'],'short'),
						$dq_info['role_title']);
				}
				$wsp_disk_quota->setInfo($this->lng->txt("enter_in_mb_desc").'<br>'.$info_text);
			}
			
			// disk usage
			include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
			$du_info = ilDiskQuotaHandler::getFilesizeByTypeAndOwner($this->object->getId());
			$disk_usage = new ilNonEditableValueGUI($lng->txt("disk_usage"), "disk_usage");
			if (!sizeof($du_info))
			{
				$disk_usage->setValue($lng->txt('unknown'));
			}
			else
			{
				require_once './Services/Utilities/classes/class.ilFormat.php';
				$disk_usage->setValue(ilFormat::formatSize(ilDiskQuotaHandler::getFilesizeByOwner($this->object->getId())));
				$info = '<table class="il_user_quota_disk_usage_overview">';
				// write the count and size of each object type
				foreach ($du_info as $detail_data)
				{
					$info .= '<tr>'.
						'<td class="std">'.$detail_data['count'].'</td>'.
						'<td class="std">'.$lng->txt("obj_".$detail_data["src_type"]).'</td>'.
						'<td class="std">'.ilFormat::formatSize($detail_data['filesize'], 'short').'</td>'.
						'</tr>'
						;
				}
				$info .= '</table>';
				$disk_usage->setInfo($info);

			}
			$this->form_gui->addItem($disk_usage);
		}
         
		// personal data
		if(
			$this->isSettingChangeable('gender') or
			$this->isSettingChangeable('firstname') or
			$this->isSettingChangeable('lastname') or
			$this->isSettingChangeable('title') or
			$this->isSettingChangeable('personal_image') or
			$this->isSettingChangeable('birhtday')
		)
		{
			$sec_pd = new ilFormSectionHeaderGUI();
			$sec_pd->setTitle($this->lng->txt("personal_data"));
			$this->form_gui->addItem($sec_pd);
		}

		// gender
		if($this->isSettingChangeable('gender'))
		{
			$gndr = new ilRadioGroupInputGUI($lng->txt("gender"), "gender");
			$gndr->setRequired(isset($settings["require_gender"]) && $settings["require_gender"]);
			$female = new ilRadioOption($lng->txt("gender_f"), "f");
			$gndr->addOption($female);
			$male = new ilRadioOption($lng->txt("gender_m"), "m");
			$gndr->addOption($male);
			$this->form_gui->addItem($gndr);
		}

		// firstname, lastname, title
		$fields = array("firstname" => true, "lastname" => true,
			"title" => isset($settings["require_title"]) && $settings["require_title"]);
		foreach($fields as $field => $req)
		{
			if($this->isSettingChangeable($field))
			{
				$inp = new ilTextInputGUI($lng->txt($field), $field);
				$inp->setSize(32);
				$inp->setMaxLength(32);
				$inp->setRequired($req);
				$this->form_gui->addItem($inp);
			}
		}

		// personal image
		if($this->isSettingChangeable('upload'))
		{
			$pi = new ilImageFileInputGUI($lng->txt("personal_picture"), "userfile");
			if ($a_mode == "edit" || $a_mode == "upload")
			{
				$pi->setImage(ilObjUser::_getPersonalPicturePath($this->object->getId(), "small", true,
					true));
			}
			$this->form_gui->addItem($pi);
		}

		if($this->isSettingChangeable('birthday'))
		{
			$birthday = new ilBirthdayInputGUI($lng->txt('birthday'), 'birthday');
			$birthday->setRequired(isset($settings["require_birthday"]) && $settings["require_birthday"]);
			$birthday->setShowEmpty(true);
			$birthday->setStartYear(1900);
			$this->form_gui->addItem($birthday);
		}


		// institution, department, street, city, zip code, country, phone office
		// phone home, phone mobile, fax, e-mail
		$fields = array(
			array("institution", 40, 80),
			array("department", 40, 80),
			array("street", 40, 40),
			array("city", 40, 40),
			array("zipcode", 10, 10),
			array("country", 40, 40),
			array("sel_country"),
			array("phone_office", 30, 30),
			array("phone_home", 30, 30),
			array("phone_mobile", 30, 30),
			array("fax", 30, 30));
			
		$counter = 0;
		foreach ($fields as $field)
		{
			if(!$counter++ and $this->isSettingChangeable($field[0]))
			{
				// contact data
				$sec_cd = new ilFormSectionHeaderGUI();
				$sec_cd->setTitle($this->lng->txt("contact_data"));
				$this->form_gui->addItem($sec_cd);
			}
			if($this->isSettingChangeable($field[0]))
			{
				// gev-patch start
				if ($field[0] == "country") {
					$inp = new ilTextInputGUI($lng->txt("federal_state"), $field[0]);
					$inp->setSize($field[1]);
					$inp->setMaxLength($field[2]);
					$inp->setRequired(isset($settings["require_".$field[0]]) &&
						$settings["require_".$field[0]]);
					$this->form_gui->addItem($inp);
				}
				else if ($field[0] == "phone_office") {
					$inp = new ilTextInputGUI($lng->txt($field[0]), $field[0]);
					$inp->setSize($field[1]);
					$inp->setMaxLength($field[2]);
					$inp->setRequired(true);
					$this->form_gui->addItem($inp);
				}else
				// gev-patch end
				if ($field[0] != "sel_country")
				{
					$inp = new ilTextInputGUI($lng->txt($field[0]), $field[0]);
					$inp->setSize($field[1]);
					$inp->setMaxLength($field[2]);
					$inp->setRequired(isset($settings["require_".$field[0]]) &&
						$settings["require_".$field[0]]);
					$this->form_gui->addItem($inp);
				}
				else
				{
					// country selection
					include_once("./Services/Form/classes/class.ilCountrySelectInputGUI.php");
					$cs = new ilCountrySelectInputGUI($lng->txt($field[0]), $field[0]);
					$cs->setRequired(isset($settings["require_".$field[0]]) &&
						$settings["require_".$field[0]]);
					$this->form_gui->addItem($cs);
				}
			}
		}

		// email
		if($this->isSettingChangeable('email'))
		{
			$em = new ilEMailInputGUI($lng->txt("email"), "email");
			$em->setRequired(isset($settings["require_email"]) &&
				$settings["require_email"]);
			$this->form_gui->addItem($em);
		}

		// interests/hobbies
		if($this->isSettingChangeable('hobby'))
		{
			$hob = new ilTextAreaInputGUI($lng->txt("hobby"), "hobby");
			$hob->setRows(3);
			$hob->setCols(40);
			$hob->setRequired(isset($settings["require_hobby"]) &&
				$settings["require_hobby"]);
			$this->form_gui->addItem($hob);
		}

		// referral comment
		if($this->isSettingChangeable('referral_comment'))
		{
			$rc = new ilTextAreaInputGUI($lng->txt("referral_comment"), "referral_comment");
			$rc->setRows(3);
			$rc->setCols(40);
			$rc->setRequired(isset($settings["require_referral_comment"]) &&
				$settings["require_referral_comment"]);
			$this->form_gui->addItem($rc);
		}

		// instant messengers
		// gev-patch start
		/*
		if($this->isSettingChangeable('instant_messengers'))
		{
			$sec_im = new ilFormSectionHeaderGUI();
			$sec_im->setTitle($this->lng->txt("instant_messengers"));
			$this->form_gui->addItem($sec_im);
		}
		*/
		// gev-patch end

		// icq, yahoo, msn, aim, skype
		$fields = array("icq", "yahoo", "msn", "aim", "skype", "jabber", "voip");
		foreach ($fields as $field)
		{
			if($this->isSettingChangeable('instant_messengers'))
			{
				$im = new ilTextInputGUI($lng->txt("im_".$field), "im_".$field);
				$im->setSize(40);
				$im->setMaxLength(40);
				$this->form_gui->addItem($im);
			}
		}

		// other information
		if($this->isSettingChangeable('user_profile_other'))
		{
			$sec_oi = new ilFormSectionHeaderGUI();
			$sec_oi->setTitle($this->lng->txt("user_profile_other"));
			$this->form_gui->addItem($sec_oi);
		}

		// matriculation number
		if($this->isSettingChangeable('matriculation'))
		{
			$mr = new ilTextInputGUI($lng->txt("matriculation"), "matriculation");
			$mr->setSize(40);
			$mr->setMaxLength(40);
			$mr->setRequired(isset($settings["require_matriculation"]) &&
				$settings["require_matriculation"]);
			$this->form_gui->addItem($mr);
		}

		// delicious
		if($this->isSettingChangeable('delicious'))
		{
			$mr = new ilTextInputGUI($lng->txt("delicious"), "delicious");
			$mr->setSize(40);
			$mr->setMaxLength(40);
			$mr->setRequired(isset($settings["require_delicious"]) &&
				$settings["require_delicious"]);
			$this->form_gui->addItem($mr);
		}

		// client IP
		$ip = new ilTextInputGUI($lng->txt("client_ip"), "client_ip");
		$ip->setSize(40);
		$ip->setMaxLength(255);
		$ip->setInfo($this->lng->txt("current_ip")." ".$_SERVER["REMOTE_ADDR"]." <br />".
			'<span class="warning">'.$this->lng->txt("current_ip_alert")."</span>");
		$this->form_gui->addItem($ip);

		// additional user defined fields
		include_once './Services/User/classes/class.ilUserDefinedFields.php';
		$user_defined_fields = ilUserDefinedFields::_getInstance();
		
		if($this->usrf_ref_id == USER_FOLDER_ID)
		{
			$all_defs = $user_defined_fields->getDefinitions();
		}
		else
		{
			$all_defs = $user_defined_fields->getChangeableLocalUserAdministrationDefinitions();
		}
	


	//gev-patch start
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$field_order = gevSettings::$UDF_FIELD_ORDER;
		$orderes_defs = array();
		$unaccounted_defs = array();


		foreach($all_defs as $field_id => $definition){
			$fname=$definition['field_name'];
			$index = array_search($fname, $field_order);
			if($index !== false){
				$ordered_defs[$index] = $definition;
			} else {
				$unaccounted_defs[] = $definition;
			}
		}
		ksort($ordered_defs);
		$all_defs = $ordered_defs + $unaccounted_defs;


		//foreach($all_defs as $field_id => $definition)
		foreach($all_defs as $field_index => $definition)
		{
			$field_id = $definition['field_id'];

	//gev-patch end


			if($definition['field_type'] == UDF_TYPE_TEXT)	// text input
			{
				$udf = new ilTextInputGUI($definition['field_name'],
					"udf_".$definition['field_id']);
				$udf->setSize(40);
				$udf->setMaxLength(255);
			}
			else if($definition['field_type'] == UDF_TYPE_WYSIWYG)	// text area input
			{
				$udf = new ilTextAreaInputGUI($definition['field_name'],
					"udf_".$definition['field_id']);
				$udf->setUseRte(true);
			}
			else			// selection input
			{
				$udf = new ilSelectInputGUI($definition['field_name'],
					"udf_".$definition['field_id']);
				$udf->setOptions($user_defined_fields->fieldValuesToSelectArray(
							$definition['field_values']));
			}

			$required = $definition['required'];
			if(!$required) {
				$required = in_array($definition['field_name'],gevSettings::$LOCAL_USER_MANDATORY_UDF_FIELDS);
			}

			$udf->setRequired($required);
			$this->form_gui->addItem($udf);
		}

		// settings
		if(
			//$a_mode == 'create' or
			$this->isSettingChangeable( 'language') or
			$this->isSettingChangeable( 'skin_style') or
			$this->isSettingChangeable( 'hits_per_page') or
			$this->isSettingChangeable( 'hide_own_online_status')
		)
		{
			$sec_st = new ilFormSectionHeaderGUI();
			$sec_st->setTitle($this->lng->txt("settings"));
			$this->form_gui->addItem($sec_st);
		}

		// role
		if ($a_mode == "create")
		{
			/*$role = new ilSelectInputGUI($lng->txt("default_role"),
				'default_role');
			$role->setRequired(true);
			$role->setValue($this->default_role);
			$role->setOptions($this->selectable_roles);
			$this->form_gui->addItem($role);*/

			if(!in_array(self::$default_role,$this->selectable_roles)){
				$this->ilias->raiseError($this->lng->txt("gev_default_role_agtid_does_not_exist"),$this->ilias->error_obj->MESSAGE);
			}

			$def_role = new ilHiddenInputGUI("default_role");
			$def_role->setValue(array_search(self::$default_role,$this->selectable_roles));
			$this->form_gui->addItem($def_role);
		}

		// language
		if($this->isSettingChangeable('language'))
		{
			$lang = new ilSelectInputGUI($lng->txt("language"),
				'language');
			$languages = $lng->getInstalledLanguages();
			$lng->loadLanguageModule("meta");
			$options = array();
			foreach($languages as $l)
			{
				$options[$l] = $lng->txt("meta_l_".$l);
			}
			$lang->setOptions($options);
			$lang->setValue($ilSetting->get("language"));
			$this->form_gui->addItem($lang);
		}

		// skin/style
		if($this->isSettingChangeable('skin_style'))
		{
			$sk = new ilSelectInputGUI($lng->txt("skin_style"),
				'skin_style');
			$templates = $styleDefinition->getAllTemplates();

			include_once("./Services/Style/classes/class.ilObjStyleSettings.php");

			$options = array();
			if (count($templates) > 0 && is_array ($templates))
			{
				foreach ($templates as $template)
				{
					$styleDef =& new ilStyleDefinition($template["id"]);
					$styleDef->startParsing();
					$styles = $styleDef->getStyles();
					foreach ($styles as $style)
					{
						if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
						{
							continue;
						}
						$options[$template["id"].":".$style["id"]] =
							$styleDef->getTemplateName()." / ".$style["name"];
					}
				}
			}
			$sk->setOptions($options);
			$sk->setValue($ilClientIniFile->readVariable("layout","skin").
				":".$ilClientIniFile->readVariable("layout","style"));
	
			$this->form_gui->addItem($sk);
		}

		// hits per page
		if($this->isSettingChangeable('hits_per_page'))
		{
			$hpp = new ilSelectInputGUI($lng->txt("hits_per_page"),
				'hits_per_page');
			$options = array(10 => 10, 15 => 15, 20 => 20, 30 => 30, 40 => 40,
				50 => 50, 100 => 100, 9999 => $this->lng->txt("no_limit"));
			$hpp->setOptions($options);
			$hpp->setValue($ilSetting->get("hits_per_page"));
			$this->form_gui->addItem($hpp);
	
			// users online
			$uo = new ilSelectInputGUI($lng->txt("users_online"),
				'show_users_online');
			$options = array(
				"y" => $lng->txt("users_online_show_y"),
				"associated" => $lng->txt("users_online_show_associated"),
				"n" => $lng->txt("users_online_show_n"));
			$uo->setOptions($options);
			$uo->setValue($ilSetting->get("show_users_online"));
			$this->form_gui->addItem($uo);
		}

		// hide online status
		if($this->isSettingChangeable('hide_own_online_status'))
		{
			$os = new ilCheckboxInputGUI($lng->txt("hide_own_online_status"), "hide_own_online_status");
			$this->form_gui->addItem($os);
		}

		// gev-patch start
		/*if((int)$ilSetting->get('session_reminder_enabled'))
		{
			$cb = new ilCheckboxInputGUI($this->lng->txt('session_reminder'), 'session_reminder_enabled');
			$cb->setValue(1);
			$this->form_gui->addItem($cb);
		}*/
		// gev-patch end
		
		// Options
		if($this->isSettingChangeable('send_mail'))
		{
			$sec_op = new ilFormSectionHeaderGUI();
			$sec_op->setTitle($this->lng->txt("options"));
			$this->form_gui->addItem($sec_op);
		}

		// send email
		if($this->isSettingChangeable('send_mail'))
		{
			$se = new ilCheckboxInputGUI($lng->txt('inform_user_mail'), 'send_mail');
			$se->setInfo($lng->txt('inform_user_mail_info'));
			$se->setValue('y');
			$se->setChecked(($ilUser->getPref('send_info_mails') == 'y'));
			$this->form_gui->addItem($se);
		}
		// gev-patch start
		// ignore required fields
		/*$irf = new ilCheckboxInputGUI($lng->txt('ignore_required_fields'), 'ignore_rf');
		$irf->setInfo($lng->txt('ignore_required_fields_info'));
		$irf->setValue(1);
		$this->form_gui->addItem($irf);*/
		// gev-patch end

		// @todo: handle all required fields

		// command buttons
		if ($a_mode == "create" || $a_mode == "save")
		{
			$this->form_gui->addCommandButton("save", $lng->txt("save"));
		}
		if ($a_mode == "edit" || $a_mode == "update")
		{
			$this->form_gui->addCommandButton("update", $lng->txt("save"));
		}
		$this->form_gui->addCommandButton("cancel", $lng->txt("cancel"));
	}
}
?>