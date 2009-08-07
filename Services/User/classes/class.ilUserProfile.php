<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	// this array should be used in all places where user data is tackled
	// in the future: registration, personal profile, user administration
	// public profile, user import/export
	// for now this is not implemented yet. Please list places, that already use it:
	//
	private static $user_field = array(
		"username" => array(
						"input" => "login",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getLogin",
						"group" => "personal_data"),
		"firstname" => array(
						"input" => "text",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getFirstname",
						"group" => "personal_data"),
		"lastname" => array(
						"input" => "text",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getLastname",
						"group" => "personal_data"),
		"title" => array(
						"input" => "text",
						"lang_var" => "person_title",
						"maxlength" => 32,
						"size" => 40,
						"method" => "getUTitle",
						"group" => "personal_data"),
		"gender" => array(
						"input" => "radio",
						"values" => array("f" => "gender_f", "m" => "gender_m"),
						"method" => "getGender",
						"group" => "personal_data"),
		"picture" => array(
						"input" => "picture",
						"group" => "personal_data"),
		"roles" => array(
						"input" => "roles",
						"group" => "personal_data"),
		"password" => array(
						"input" => "password",
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
						"group" => "contact_data"),
		"referral_comment" => array(
						"input" => "textarea",
						"rows" => 3,
						"cols" => 45,
						"method" => "getComment",
						"group" => "contact_data"),
		"im" => array(
						"input" => "messenger",
						"types" => array("icq","yahoo","msn","aim","skype","jabber","voip"), 
						"maxlength" => 40,
						"size" => 40,
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
						"group" => "settings"),
		"skinstyle" => array(
						"input" => "skinstyle",
						"group" => "settings"),
		"hitsperpage" => array(
						"input" => "selection",
						"group" => "settings"),
		"showactiveusers" => array(
						"input" => "selection",
						"group" => "settings"),
		"hideonlinestatus" => array(
						"input" => "selection",
						"group" => "settings"),
		"preferences" => array(
						"group" => "preferences")
		
		);
		
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->hidden_groups = array();
	}
	
	/**
	 * Set omit password
	 *
	 * @param	boolean		no password
	 */
	function setOmitPassword($a_val)
	{
		$this->omit_password = $a_val;
	}
	
	/**
	 * Get omit password
	 *
	 * @return	boolean		no password
	 */
	function getOmitPassword()
	{
		return $this->omit_password;
	}

	/**
	 * Get standard user fields array
	 */
	static function getStandardFields()
	{
		return self::$user_field;
	}
	
	/**
	 * Hide a group
	 */
	function hideGroup($a_group)
	{
		$this->hidden_groups[] = $a_group;
	}
	
	/**
	* Add standard fields to form
	*/
	function addStandardFieldsToForm($a_form, $a_user)
	{
		global $ilSetting, $lng, $rbacreview, $ilias;
		
		$fields = ilUserProfile::getStandardFields();
		
		$current_group = "";
		foreach ($fields as $f => $p)
		{
			// skip hidden groups
			if (in_array($p["group"], $this->hidden_groups))
			{
				continue;
			}
			
			// next group? -> diplay subheader
			if ($p["group"] != $current_group)
			{
				// contact data
				$sh = new ilFormSectionHeaderGUI();
				$sh->setTitle($lng->txt($p["group"]));
				$a_form->addItem($sh);
				$current_group = $p["group"];
			}
			
			$m = $p["method"];
			
			$lv = ($p["lang_var"] != "")
				? $p["lang_var"]
				: $f;
			
			switch ($p["input"])
			{
				case "login":
					if ((int)$ilSetting->get('allow_change_loginname'))
					{
						$val = new ilTextInputGUI($lng->txt('username'),'username');
						$val->setValue($a_user->getLogin());
						$val->setMaxLength(32);
						$val->setSize(40);
						$val->setRequired(true);
					}
					else
					{
						// user account name
						$val = new ilNonEditableValueGUI($lng->txt("username"));	
						$val->setValue($a_user->getLogin());
					}
					$a_form->addItem($val);
					break;
				
				case "text":
					if (ilUserProfile::userSettingVisible($f))
					{
						$ti = new ilTextInputGUI($lng->txt($lv), "usr_".$f);
						$ti->setValue($a_user->$m());
						$ti->setMaxLength($p["maxlength"]);
						$ti->setSize($p["size"]);
						$ti->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						$ti->setRequired($ilSetting->get("require_".$f));
						$a_form->addItem($ti);
					}
					break;
					
				case "radio":
					if (ilUserProfile::userSettingVisible($f))
					{
						$rg = new ilRadioGroupInputGUI($lng->txt($lv), "usr_".$f);
						$rg->setValue($a_user->$m());
						foreach  ($p["values"] as $k => $v)
						{
							$op = new ilRadioOption($lng->txt($v), $k);
							$rg->addOption($op);
						}
						$rg->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						$rg->setRequired($ilSetting->get("require_".$f));
						$a_form->addItem($rg);
					}
					break;
					
				case "picture":
					if (ilUserProfile::userSettingVisible("upload"))
					{
						$ii = new ilImageFileInputGUI($lng->txt("personal_picture"), "userfile");
						$im = ilObjUser::_getPersonalPicturePath($a_user->getId(), "small", true,
							true);
						$ii->setDisabled($ilSetting->get("usr_settings_disable_upload"));
						if ($im != "")
						{
							$ii->setImage($im);
							$ii->setAlt($lng->txt("personal_picture"));
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
					$dr = new ilNonEditableValueGUI($lng->txt("default_roles"));
					$dr->setValue(substr($role_names,0,-2));
					$a_form->addItem($dr);
					break;
					
				case "email":
					if (ilUserProfile::userSettingVisible($f))
					{
						$em = new ilTextInputGUI($lng->txt($lv), "usr_".$f);
						$em->setValue($a_user->$m());
						$em->setMaxLength($p["maxlength"]);
						$em->setSize($p["size"]);
						$em->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						$em->setRequired($ilSetting->get("require_".$f));
						$a_form->addItem($em);
					}
					break;
					
				case "textarea":
					if (ilUserProfile::userSettingVisible($f))
					{
						$ta = new ilTextAreaInputGUI($lng->txt($lv), "usr_".$f);
						$ta->setValue($a_user->$m());
						$ta->setRows($p["rows"]);
						$ta->setCols($p["cols"]);
						$ta->setDisabled($ilSetting->get("usr_settings_disable_".$f));
						$ta->setRequired($ilSetting->get("require_".$f));
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
							$im->setValue($a_user->getInstantMessengerId($im_name));
							$im->setMaxLength($p["maxlength"]);
							$im->setSize($p["size"]);
							$im->setDisabled($ilSetting->get("usr_settings_disable_"."instant_messengers"));
							$im->setRequired($ilSetting->get("require_"."instant_messengers"));
							$a_form->addItem($im);
						}
					}
					break;
					
				case "password":
					if (!$this->getOmitPassword())
					{
						// todo
					}
					break;
			}
		}
	}
	
	/**
	* Checks whether user setting is visible
	*/
	static function userSettingVisible($a_setting)
	{
		global $ilSetting;
		
		return ($ilSetting->get("usr_settings_hide_".$a_setting) != 1);
	}
}
?>
