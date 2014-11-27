<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

// mjansen@databay.de essential for mail constants, do not remove this include
include_once 'Services/Mail/classes/class.ilMailOptions.php';

/**
 * Class ilUserProfile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilUserProfile
{
	const MODE_DESKTOP = 1;
	const MODE_REGISTRATION = 2;

	private static $mode = self::MODE_DESKTOP;

	// this array should be used in all places where user data is tackled
	// in the future: registration, personal profile, user administration
	// public profile, user import/export
	// for now this is not implemented yet. Please list places, that already use it:
	//
	// - personal profile
	// - (global) standard user profile fields settings
	//
	// the following attributes are defined (can be extended if needed):
	// - input: input type
	//			standard inputs: text, radio, selection, textarea
	//			special inputs: login
	// - input dependend attributes
	//		- maxlength, sizte for text
	//		- values array for radio
	//		- cols/rows for text areas
	//		- options array for selections
	// - method: ilObjUser get-method, e.g. getFirstname
	// - group: group id (id is also used as lang_var for sub headers in forms
	// - lang_var: if key should not be used as lang var, this overwrites the usage in forms
	// - settings property related attributes, settingsproperties are ("visible", "changeable",
	//   "searchable", "required", "export", "course_export" and "registration")
	// 		- <settingsproperty>_hide: hide this property in settings (not implemented)
	// 		- <settingsproperty>_fix_value: property has a fix value (cannot be changed)
	private static $user_field = array(
		"username" => array(
						"input" => "login",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getLogin",
						"course_export_fix_value" => 1,
						"group_export_fix_value" => 1,
						"changeable_hide" => true,
						"required_hide" => true,
						"group" => "personal_data"),
		"password" => array(
						"input" => "password",
						"required_hide" => true,
						"visib_reg_hide" => true,
						'visib_lua_fix_value' => 0,
						"course_export_hide" => true,
						"export_hide" => false,
						"group_export_hide" => true,
						"lists_hide" => true,
						"group" => "personal_data"),
		"firstname" => array(
						"input" => "text",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getFirstname",
						"required_fix_value" => 1,
						"visib_reg_fix_value" => 1,
						'visib_lua_fix_value' => 1,
						"course_export_fix_value" => 1,
						"group_export_fix_value" => 1,
						"group" => "personal_data"),
		"lastname" => array(
						"input" => "text",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getLastname",
						"required_fix_value" => 1,
						"visib_reg_fix_value" => 1,
						'visib_lua_fix_value' => 1,
						"course_export_fix_value" => 1,
						"group_export_fix_value" => 1,
						"group" => "personal_data"),
		"title" => array(
						"input" => "text",
						"lang_var" => "person_title",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getUTitle",
						"group" => "personal_data"),
		"birthday" => array(
						"input" => "birthday",
						"lang_var" => "birthday",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getBirthday",
						"group" => "personal_data"),
		"gender" => array(
						"input" => "radio",
						"values" => array("f" => "gender_f", "m" => "gender_m"),
						"method" => "getGender",
						"group" => "personal_data"),
		"upload" => array(
						"input" => "picture",
						"required_hide" => true,
						"visib_reg_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"lists_hide" => true,
						"lang_var" => "personal_picture",
						"group" => "personal_data"),
		"roles" => array(
						"input" => "roles",
						"changeable_hide" => true,
						"required_hide" => true,
						"visib_reg_hide" => true,
						"export_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => TRUE,
						"lists_hide" => true,
						"group" => "personal_data"),
		"institution" => array(
						"input" => "text",
						"maxlength" => 80,
						"size" => 40,
						"method" => "getInstitution",
						"group" => "contact_data"),
		"department" => array(
						"input" => "text",
						"maxlength" => 80,
						"size" => 40,
						"method" => "getDepartment",
						"group" => "contact_data"),
		"street" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getStreet",
						"group" => "contact_data"),
		"zipcode" => array(
						"input" => "text",
						"maxlength" => 10,
						"size" => 10,
						"method" => "getZipcode",
						"group" => "contact_data"),
		"city" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getCity",
						"group" => "contact_data"),
		"country" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getCountry",
						"group" => "contact_data"),
		"sel_country" => array(
						"input" => "sel_country",
						"method" => "getSelectedCountry",
						"group" => "contact_data"),
		"phone_office" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getPhoneOffice",
						"group" => "contact_data"),
		"phone_home" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getPhoneHome",
						"group" => "contact_data"),
		"phone_mobile" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getPhoneMobile",
						"group" => "contact_data"),
		"fax" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getFax",
						"group" => "contact_data"),
		"email" => array(
						"input" => "email",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getEmail",
						"group" => "contact_data"),
		"hobby" => array(
						"input" => "textarea",
						"rows" => 3,
						"cols" => 45,
						"method" => "getHobby",
						"lists_hide" => true,
						"group" => "contact_data"),
		"referral_comment" => array(
						"input" => "textarea",
						"rows" => 3,
						"cols" => 45,
						"method" => "getComment",
						"course_export_hide" => true,
						"group_export_hide" => true,
						"lists_hide" => true,
						"group" => "contact_data"),
		"interests_general" => array(
						"input" => "multitext",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getGeneralInterests",
						"course_export_hide" => true,
						"group_export_hide" => true,
						"lists_hide" => true,														
						"group" => "interests"),
		"interests_help_offered" => array(
						"input" => "multitext",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getOfferingHelp",
						"course_export_hide" => true,
						"group_export_hide" => true,						
						"lists_hide" => true,									
						"group" => "interests"),
		"interests_help_looking" => array(
						"input" => "multitext",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getLookingForHelp",
						"course_export_hide" => true,
						"group_export_hide" => true,
						"lists_hide" => true,								
						"group" => "interests"),
		"instant_messengers" => array(
						"input" => "messenger",
						"types" => array("icq","yahoo","msn","aim","skype","jabber","voip"), 
						"maxlength" => 40,
						"size" => 40,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"lists_hide" => true,
						"group" => "instant_messengers"),
		"matriculation" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getMatriculation",
						"group" => "other"),
		"delicious" => array(
						"input" => "text",
						"maxlength" => 40,
						"size" => 40,
						"method" => "getDelicious",
						"group" => "other"),
		"language" => array(
						"input" => "language",
						"method" => "getLanguage",
						"required_hide" => true,
						"visib_reg_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"group" => "settings"),
		"skin_style" => array(
						"input" => "skinstyle",
						"required_hide" => true,
						"visib_reg_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"group" => "settings"),
		"hits_per_page" => array(
						"input" => "hitsperpage",
						"default" => 10,
						"options" => array(
							10 => 10, 15 => 15, 20 => 20, 30 => 30, 40 => 40,
							50 => 50, 100 => 100, 9999 => 9999),
						"required_hide" => true,
						"visib_reg_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"group" => "settings"),
		"show_users_online" => array(
						"input" => "selection",
						"default" => "y",
						"options" => array(
							"y" => "users_online_show_short_y",
							"associated" => "users_online_show_short_associated",
							"n" => "users_online_show_short_n"),
						"required_hide" => true,
						"visib_reg_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"group" => "settings"),
		"hide_own_online_status" => array(
						"input" => "selection",
						"required_hide" => true,
						"visib_reg_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"group" => "settings"),
		"preferences" => array(
						"visible_fix_value" => 1,
						"changeable_fix_value" => 1,
						"required_hide" => true,
						"visib_reg_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"group" => "preferences"),
		"mail_incoming_mail" => array(
						"input" => "selection",
						"default" => "y",
						"options" => array(
							IL_MAIL_LOCAL => "mail_incoming_local",
							IL_MAIL_EMAIL => "mail_incoming_smtp",
							IL_MAIL_BOTH => "mail_incoming_both"),
						"required_hide" => true,
						"visib_reg_hide" => true,
						"course_export_hide" => true,
						"group_export_hide" => true,
						"export_hide" => true,
						"search_hide" => true,
						"group" => "settings")
		
		);
		
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->skip_groups = array();
		$this->skip_fields = array();
	}
	
	/**
	 * Get standard user fields array
	 */
	function getStandardFields()
	{
		$fields = array();
		foreach (self::$user_field as $f => $p)
		{
			// skip hidden groups
			if (in_array($p["group"], $this->skip_groups) ||
				in_array($f, $this->skip_fields))
			{
				continue;
			}
			$fields[$f] = $p;
		}
		return $fields;
	}
	
	/**
	 * Get visible fields in local user administration
	 * @return 
	 */
	public function getLocalUserAdministrationFields()
	{
		global $ilSetting;
		
		$settings = $ilSetting->getAll();
		
		$fields = array();
		foreach($this->getStandardFields() as $field => $info)
		{
			if($ilSetting->get('usr_settings_visib_lua_'.$field,1))
			{
				$fields[$field] = $info;
			}
			elseif($info['visib_lua_fix_value'])
			{
				$fields[$field] = $info;
			}
		}
		return $fields;
	}
	
	
	/**
	 * Skip a group
	 */
	function skipGroup($a_group)
	{
		$this->skip_groups[] = $a_group;
	}

	/**
	 * Skip a field
	 */
	function skipField($a_field)
	{
		$this->skip_fields[] = $a_field;
	}
	
	/**
	* Add standard fields to form
	*/
	function addStandardFieldsToForm($a_form, $a_user = NULL, array $custom_fields = NULL)
	{
		global $ilSetting, $lng, $rbacreview, $ilias;

		// custom registration settings
		if(self::$mode == self::MODE_REGISTRATION)
		{
			include_once 'Services/Registration/classes/class.ilRegistrationSettings.php';
			$registration_settings = new ilRegistrationSettings();

			self::$user_field["username"]["group"] = "login_data";
			self::$user_field["password"]["group"] = "login_data";
			self::$user_field["language"]["default"] = $lng->lang_key;

			// different position for role
			$roles = self::$user_field["roles"];
			unset(self::$user_field["roles"]);
			self::$user_field["roles"] = $roles;
			self::$user_field["roles"]["group"] = "settings";			
		}
		
		$fields = $this->getStandardFields();
		$current_group = "";
		$custom_fields_done = false;
		foreach ($fields as $f => $p)
		{
			// next group? -> diplay subheader
			if (($p["group"] != $current_group) &&
				ilUserProfile::userSettingVisible($f))
			{
				if (is_array($custom_fields) && !$custom_fields_done)
				{
					// should be appended to "other" or at least before "settings"
					if($current_group == "other" || $p["group"] == "settings")
					{
						// add "other" subheader
						if($current_group != "other")
						{
							$sh = new ilFormSectionHeaderGUI();
							$sh->setTitle($lng->txt("other"));
							$a_form->addItem($sh);
						}
						foreach($custom_fields as $custom_field)
						{
							$a_form->addItem($custom_field);
						}
						$custom_fields_done = true;
					}
				}
				
				$sh = new ilFormSectionHeaderGUI();
				$sh->setTitle($lng->txt($p["group"]));
				$a_form->addItem($sh);
				$current_group = $p["group"];
			}

			$m = "";
			if (isset($p["method"]))
			{
				$m = $p["method"];
			}
			
			$lv = (isset($p["lang_var"]) && $p["lang_var"] != "")
				? $p["lang_var"]
				: $f;
			
			switch ($p["input"])
			{
				case "login":
					if ((int)$ilSetting->get('allow_change_loginname') || self::$mode == self::MODE_REGISTRATION)
					{
						$val = new ilTextInputGUI($lng->txt('username'),'username');
						if($a_user)
						{
							$val->setValue($a_user->getLogin());
						}
						$val->setMaxLength(32);
						$val->setSize(40);
						$val->setRequired(true);
					}
					else
					{
						// user account name
						$val = new ilNonEditableValueGUI($lng->txt("username"), 'ne_un');
						if($a_user)
						{
							$val->setValue($a_user->getLogin());
						}
					}
					$a_form->addItem($val);
					break;
				
				case "text":
					if (ilUserProfile::userSettingVisible($f))
					{
						$ti = new ilTextInputGUI($lng->txt($lv), "usr_".$f);
						if($a_user)
						{
							$ti->setValue($a_user->$m());
						}
						$ti->setMaxLength($p["maxlength"]);
						$ti->setSize($p["size"]);
						$ti->setRequired($ilSetting->get("require_".$f));					
						if(!$ti->getRequired() || $ti->getValue())
						{
							$ti->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}
						$a_form->addItem($ti);
					}
					break;

				case "sel_country":
					if (ilUserProfile::userSettingVisible($f))
					{
						include_once("./Services/Form/classes/class.ilCountrySelectInputGUI.php");
						$ci = new ilCountrySelectInputGUI($lng->txt($lv), "usr_".$f);
						if($a_user)
						{
							$ci->setValue($a_user->$m());
						}
						$ci->setRequired($ilSetting->get("require_".$f));
						if(!$ci->getRequired() || $ci->getValue())
						{
							$ci->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}
						$a_form->addItem($ci);
					}
					break;

				case "birthday":
					if (ilUserProfile::userSettingVisible($f))
					{
						$bi = new ilBirthdayInputGUI($lng->txt($lv), "usr_".$f);
						include_once "./Services/Calendar/classes/class.ilDateTime.php";
						$date = null;
						if ($a_user && strlen($a_user->$m()))
						{
							$date = new ilDateTime($a_user->$m(), IL_CAL_DATE);
							$bi->setDate($date);
						}
						$bi->setShowEmpty(true);
						$bi->setStartYear(1900);
						$bi->setRequired($ilSetting->get("require_".$f));
						if(!$bi->getRequired() || $date)
						{
							$bi->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}
						$a_form->addItem($bi);
					}
					break;
					
				case "radio":
					if (ilUserProfile::userSettingVisible($f))
					{
						$rg = new ilRadioGroupInputGUI($lng->txt($lv), "usr_".$f);
						if($a_user)
						{
							$rg->setValue($a_user->$m());
						}
						foreach  ($p["values"] as $k => $v)
						{
							$op = new ilRadioOption($lng->txt($v), $k);
							$rg->addOption($op);
						}
						$rg->setRequired($ilSetting->get("require_".$f));
						if(!$rg->getRequired() || $rg->getValue())
						{
							$rg->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}
						$a_form->addItem($rg);
					}
					break;
					
				case "picture":
					if (ilUserProfile::userSettingVisible("upload") && $a_user)
					{
						$ii = new ilImageFileInputGUI($lng->txt("personal_picture"), "userfile");
						$ii->setDisabled($ilSetting->get("usr_settings_disable_upload"));
						
						$upload = $a_form->getFileUpload("userfile");
						if($upload["name"])
						{
							$ii->setPending($upload["name"]);
						}
						else
						{
							$im = ilObjUser::_getPersonalPicturePath($a_user->getId(), "small", true,
								true);
							if ($im != "")
							{
								$ii->setImage($im);
								$ii->setAlt($lng->txt("personal_picture"));
							}
						}
			
						// ilinc link as info
						if (ilUserProfile::userSettingVisible("upload") and
							$ilSetting->get("ilinc_active"))
						{
							include_once ('./Modules/ILinc/classes/class.ilObjiLincUser.php');
							$ilinc_user = new ilObjiLincUser($a_user);
			
							if ($ilinc_user->id)
							{
								include_once ('./Modules/ILinc/classes/class.ilnetucateXMLAPI.php');
								$ilincAPI = new ilnetucateXMLAPI();
								$ilincAPI->uploadPicture($ilinc_user);
								$response = $ilincAPI->sendRequest("uploadPicture");
			
								// return URL to user's personal page
								$url = trim($response->data['url']['cdata']);
								$desc =
									$lng->txt("ilinc_upload_pic_text")." ".
									'<a href="'.$url.'">'.$lng->txt("ilinc_upload_pic_linktext").'</a>';
								$ii->setInfo($desc);
							}
						}
			
						$a_form->addItem($ii);
					}
					break;
					
				case "roles":
					if(self::$mode == self::MODE_DESKTOP)
					{
						if (ilUserProfile::userSettingVisible("roles"))
						{
							$global_roles = $rbacreview->getGlobalRoles();
							foreach($global_roles as $role_id)
							{
								if (in_array($role_id,$rbacreview->assignedRoles($a_user->getId())))
								{
									$roleObj = $ilias->obj_factory->getInstanceByObjId($role_id);
									$role_names .= $roleObj->getTitle().", ";
									unset($roleObj);
								}
							}
							$dr = new ilNonEditableValueGUI($lng->txt("default_roles"), "ne_dr");
							$dr->setValue(substr($role_names,0,-2));
							$a_form->addItem($dr);
						}
					}
					else if(self::$mode == self::MODE_REGISTRATION)
					{
						if($registration_settings->roleSelectionEnabled())
						{
							include_once("./Services/AccessControl/classes/class.ilObjRole.php");
							$options = array();
							foreach (ilObjRole::_lookupRegisterAllowed() as $role)
							{
								$options[$role["id"]] = $role["title"];
							}
							// registration form validation will take care of missing field / value
							if($options)
							{
								if(sizeof($options) > 1)
								{
									$ta = new ilSelectInputGUI($lng->txt('default_role'), "usr_".$f);
									$ta->setOptions($options);
									$ta->setRequired($ilSetting->get("require_".$f));
									if(!$ta->getRequired())
									{
										$ta->setDisabled($ilSetting->get("usr_settings_disable_".$f));
									}
								}
								// no need for select if only 1 option
								else
								{
									$ta = new ilHiddenInputGUI("usr_".$f);		
									$ta->setValue(array_shift(array_keys($options)));
								}
								$a_form->addItem($ta);
							}
						}
					}
					break;
					
				case "email":
					if (ilUserProfile::userSettingVisible($f))
					{
						$em = new ilEMailInputGUI($lng->txt($lv), "usr_".$f);
						if($a_user)
						{
							$em->setValue($a_user->$m());
						}
						$em->setRequired($ilSetting->get("require_".$f));
						if(!$em->getRequired() || $em->getValue())
						{
							$em->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}
						if(self::MODE_REGISTRATION == self::$mode)
						{
							$em->setRetype(true);
						}
						$a_form->addItem($em);
					}
					break;
					
				case "textarea":
					if (ilUserProfile::userSettingVisible($f))
					{
						$ta = new ilTextAreaInputGUI($lng->txt($lv), "usr_".$f);
						if($a_user)
						{
							$ta->setValue($a_user->$m());
						}
						$ta->setRows($p["rows"]);
						$ta->setCols($p["cols"]);
						$ta->setRequired($ilSetting->get("require_".$f));
						if(!$ta->getRequired() || $ta->getValue())
						{
							$ta->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}
						$a_form->addItem($ta);
					}
					break;
					
				case "messenger":
					if (ilUserProfile::userSettingVisible("instant_messengers"))
					{
						$im_arr = $p["types"];
						foreach ($im_arr as $im_name)
						{
							$im = new ilTextInputGUI($lng->txt("im_".$im_name), "usr_im_".$im_name);
							if($a_user)
							{
								$im->setValue($a_user->getInstantMessengerId($im_name));
							}
							$im->setMaxLength($p["maxlength"]);
							$im->setSize($p["size"]);
							$im->setRequired($ilSetting->get("require_"."instant_messengers"));
							if(!$im->getRequired() || $im->getValue())
							{
								$im->setDisabled($ilSetting->get("usr_settings_disable_"."instant_messengers"));
							}
							$a_form->addItem($im);
						}
					}
					break;
					
				case "password":
					if (self::$mode == self::MODE_REGISTRATION)
					{
						if(!$registration_settings->passwordGenerationEnabled())
						{
							$ta = new ilPasswordInputGUI($lng->txt($lv), "usr_".$f);							
							$ta->setRequired(true);
							// $ta->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}
						else
						{
							$ta = new ilNonEditableValueGUI($lng->txt($lv));
							$ta->setValue($lng->txt("reg_passwd_via_mail"));
						}
						$a_form->addItem($ta);
					}
					break;
					
				case "language":
					if (ilUserProfile::userSettingVisible($f))
					{
						$ta = new ilSelectInputGUI($lng->txt($lv), "usr_".$f);
						if($a_user)
						{
							$ta->setValue($a_user->$m());
						}
						$options = array();
						$lng->loadLanguageModule("meta");
						foreach ($lng->getInstalledLanguages() as $lang_key)
						{
							$options[$lang_key] = $lng->txt("meta_l_".$lang_key);
						}
						asort($options); // #9728
						$ta->setOptions($options);
						$ta->setRequired($ilSetting->get("require_".$f));
						if(!$ta->getRequired() || $ta->getValue())
						{
							$ta->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}
						$a_form->addItem($ta);
					}
					break;
					
				case "multitext":
					if (ilUserProfile::userSettingVisible($f))
					{
						$ti = new ilTextInputGUI($lng->txt($lv), "usr_".$f);
						$ti->setMulti(true);
						if($a_user)
						{
							$ti->setValue($a_user->$m());
						}
						$ti->setMaxLength($p["maxlength"]);
						$ti->setSize($p["size"]);
						$ti->setRequired($ilSetting->get("require_".$f));					
						if(!$ti->getRequired() || $ti->getValue())
						{
							$ti->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						}					 		
						if($this->ajax_href)
						{
							// add field to ajax call
							$ti->setDataSource($this->ajax_href."&f=".$f);
						}
						$a_form->addItem($ti);
					}
					break;
			}
		}
		
		// append custom fields as "other"
		if(is_array($custom_fields) && !$custom_fields_done)
		{
			// add "other" subheader
			if($current_group != "other")
			{
				$sh = new ilFormSectionHeaderGUI();
				$sh->setTitle($lng->txt("other"));
				$a_form->addItem($sh);
			}
			foreach($custom_fields as $custom_field)
			{
				$a_form->addItem($custom_field);
			}
		}
	}
	
	public function setAjaxCallback($a_href)
	{
		$this->ajax_href = $a_href;
	}
	
	/**
	* Checks whether user setting is visible
	*/
	static function userSettingVisible($a_setting)
	{
		global $ilSetting;

		if(self::$mode == self::MODE_DESKTOP)
		{
			return ($ilSetting->get("usr_settings_hide_".$a_setting) != 1);
		}
		else
		{
			if(isset(self::$user_field[$a_setting]["visib_reg_hide"]) && self::$user_field[$a_setting]["visib_reg_hide"] === true)
			{
				return true;
			}
			return ($ilSetting->get("usr_settings_visib_reg_".$a_setting, "1") || $ilSetting->get("require_".$a_setting, "0"));
		}
	}
	
	static function setMode($mode)
	{
		global $lng;

		if(in_array($mode, array(self::MODE_DESKTOP, self::MODE_REGISTRATION)))
		{
			self::$mode = $mode;
			return true;
		}
		return false;
	}
		
	/**
	 * Check if all required personal data fields are set
	 * 
	 * @param ilObjUser $a_user
	 * @param bool $a_include_udf check custom fields, too
	 * @param bool $a_personal_data_only only check fields which are visible in personal data
	 * @return bool 
	 */
	static function isProfileIncomplete($a_user, $a_include_udf = true, $a_personal_data_only = true)
	{
		global $ilSetting;
		
		// standard fields
		foreach(self::$user_field as $field => $definition)
		{
			// only if visible in personal data
			if($a_personal_data_only && $ilSetting->get("usr_settings_hide_".$field))
			{
				continue;
			}
			
			if($ilSetting->get("require_".$field) && $definition["method"])
			{
				$value = $a_user->{$definition["method"]}();
				if($value == "")
				{
					return true;
				}				
			}
		}
		
		// custom fields
		if($a_include_udf)
		{
			$user_defined_data = $a_user->getUserDefinedData();
						
			include_once './Services/User/classes/class.ilUserDefinedFields.php';
			$user_defined_fields = ilUserDefinedFields::_getInstance();						
			foreach($user_defined_fields->getRequiredDefinitions() as $field => $definition)
			{
				// only if visible in personal data
				if($a_personal_data_only && !$definition["visible"])
				{
					continue;
				}
				
				if(!$user_defined_data["f_".$field])
				{
					return true;
				}				
			}					
		}
		
		return false;
	}
	
	/**
	 * 
	 * Returns whether a profile setting is editable by an user in the profile gui
	 * 
	 * @param	string	A key of a profile setting
	 * @return	boolean	Determines whether the passed setting can be edited by the user itself
	 * @access	protected
	 * @static
	 * 
	 */
	protected static function isEditableByUser($setting)
	{
		/**
		 * 
		 * @global	ilSetting
		 * 
		 */
		global $ilSetting;
		
		// Not visible in personal data or not changeable
		if( $ilSetting->get('usr_settings_hide_'.$setting) == 1 ||
			$ilSetting->get('usr_settings_disable_'.$setting) == 1 )
		{
			// User has no chance to edit this field
			return false;
		}
		
		return true;
	}
	
	/**
	 * 
	 * Returns an array of all ignorable profiel fields
	 * 
	 * @return	array
	 * @access	public
	 * @static
	 * 
	 */
	public static function getIgnorableRequiredSettings()
	{
		/**
		 * 
		 * @global	ilSetting
		 * 
		 */
		global $ilSetting;
		
		$ignorableSettings = array();
	
		foreach(self::$user_field as $field => $definition)
		{
			// !!!username and password must not be ignored!!!
			if( 'username' == $field ||
				'password' == $field )
			{
				continue;
			}
			
			// Field is not required -> continue
			if( !$ilSetting->get('require_'.$field) )
			{
				continue;
			}
			
			if( self::isEditableByUser($field) )
			{			
				$ignorableSettings[] = $field;
			}
		}
		
		return $ignorableSettings;
	}
}
?>
