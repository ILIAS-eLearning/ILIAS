<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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


/**
* change user profile
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
// catch hack attempts
if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
{
	$ilias->raiseError($lng->txt("msg_not_available_for_anon"),$ilias->error_obj->MESSAGE);
}

$strip = false;

if (!empty($_POST))
{
	$strip = true;
}

foreach($_POST as $key => $val)
{
//	$_POST[$key] = ilUtil::prepareFormOutput($val,$strip);
}

$webspace_dir = $ilias->ini->readVariable("server","webspace_dir");

//load ILIAS settings
$settings = $ilias->getAllSettings();

//$image_dir = $webspace_dir."/usr_images";

// Returns TRUE if working with the given
// user setting is allowed, FALSE otherwise
function workWithUserSetting($setting)
{
	global $settings;
	$result = TRUE;
	if ($settings["usr_settings_hide_".$setting] == 1)
	{
		$result = FALSE;
	}
	if ($settings["usr_settings_disable_".$setting] == 1)
	{
		$result = FALSE;
	}
	return $result;
}

function userSettingVisible($setting)
{
	global $settings;
	$result = TRUE;
	if ($settings["usr_settings_hide_".$setting] == 1)
	{
		$result = FALSE;
	}
	return $result;
}

function userSettingEnabled($setting)
{
	global $settings;
	$result = TRUE;
	if ($settings["usr_settings_disable_".$setting] == 1)
	{
		$result = FALSE;
	}
	return $result;
}

// purpose is to upload file of user
// function added by ratana ty
function upload_file()
{
	global $upload_error,$lng;
	global $userfile, $userfile_name, $userfile_size,
	$userfile_type, $archive_dir, $WINDIR,$ilias,$lng;
	global $target_file, $return_path;

	if ($_FILES["userfile"]["size"] == 0)
	{
		$upload_error=$lng->txt("msg_no_file");
		return;
	}

// TODO
// Check the type of file and then check the size
// of the file whether we allow people to upload or not

	$webspace_dir = ilUtil::getWebspaceDir();
	$image_dir = $webspace_dir."/usr_images";
	$target_file = $image_dir."/usr_".$ilias->account->getId()."."."jpg";
	$store_file = "usr_".$ilias->account->getID()."."."jpg";

	// store filename
	$ilias->account->setPref("profile_image", $store_file);
	$ilias->account->update();

	//$tempfile = tempnam ("/tmp", "usr_profile_");
	//$pathinfo = pathinfo($tempfile);
	
	//
	$uploaded_file = $image_dir."/upload_".$ilias->account->getId();
//echo ":".$uploaded_file.":";
	if (!ilUtil::moveUploadedFile($_FILES["userfile"]["tmp_name"], $_FILES["userfile"]["name"],
		$uploaded_file, false))
	{
		ilUtil::redirect("usr_profile.php");
	}
	//move_uploaded_file($_FILES["userfile"]["tmp_name"],
	//	$uploaded_file);
	chmod($uploaded_file, 0770);

	// take quality 100 to avoid jpeg artefacts when uploading jpeg files
	// taking only frame [0] to avoid problems with animated gifs
	$show_file  = "$image_dir/usr_".$ilias->account->getId().".jpg"; 
	$thumb_file = "$image_dir/usr_".$ilias->account->getId()."_small.jpg";
	$xthumb_file = "$image_dir/usr_".$ilias->account->getId()."_xsmall.jpg"; 
	$xxthumb_file = "$image_dir/usr_".$ilias->account->getId()."_xxsmall.jpg";

	system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 200x200 -quality 100 JPEG:$show_file");
	system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 100x100 -quality 100 JPEG:$thumb_file");
	system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 75x75 -quality 100 JPEG:$xthumb_file");
	system(ilUtil::getConvertCmd()." $uploaded_file" . "[0] -geometry 30x30 -quality 100 JPEG:$xxthumb_file");

	if ($error)
	{
		//$ilias->raiseError($lng->txt("image_gen_unsucc"), $ilias->error_obj->MESSAGE);
		sendInfo($lng->txt("image_gen_unsucc"), true);
		ilUtil::redirect("usr_profile.php");
	}

	return $target_file;
}

function removePicture()
{
	global $ilias;

	$webspace_dir = ilUtil::getWebspaceDir();
	$image_dir = $webspace_dir."/usr_images";
	$file = $image_dir."/usr_".$ilias->account->getID()."."."jpg";
	$thumb_file = $image_dir."/usr_".$ilias->account->getID()."_small.jpg";
	$xthumb_file = $image_dir."/usr_".$ilias->account->getID()."_xsmall.jpg";
	$xxthumb_file = $image_dir."/usr_".$ilias->account->getID()."_xxsmall.jpg";
	$upload_file = $image_dir."/upload_".$ilias->account->getID();

	// remove user pref file name
	$ilias->account->setPref("profile_image", "");
	$ilias->account->update();

	if (@is_file($file))
	{
		unlink($file);
	}
	if (@is_file($thumb_file))
	{
		unlink($thumb_file);
	}
	if (@is_file($xthumb_file))
	{
		unlink($xthumb_file);
	}
	if (@is_file($xxthumb_file))
	{
		unlink($xxthumb_file);
	}
	if (@is_file($upload_file))
	{
		unlink($upload_file);
	}

}

// End of function upload file

// change user password
function change_password()
{
	global $ilias, $lng, $tpl, $password_error;
	
	// do nothing if auth mode is not local database
	if ($ilias->account->getAuthMode(true) != AUTH_LOCAL)
	{
		return;
	}

    // select password from auto generated passwords
    if ($ilias->getSetting("passwd_auto_generate") == 1)
    {
    	// check old password
        if (md5($_POST["current_password"]) != $ilias->account->getPasswd())
        {
            $password_error=$lng->txt("passwd_wrong");
            //$ilias->raiseError($lng->txt("passwd_wrong"),$ilias->error_obj->MESSAGE);
        }

        // validate transmitted password
        if (!ilUtil::isPassword($_POST["new_passwd"]))
        {
            $password_error=$lng->txt("passwd_not_selected");
            //$ilias->raiseError($lng->txt("passwd_not_selected"),$ilias->error_obj->MESSAGE);
        }
        
        if (empty($password_error))
        {
            $ilias->account->updatePassword($_POST["current_password"], $_POST["new_passwd"], $_POST["new_passwd"]);
        }
    }
    else
    {

	// check old password
	if (md5($_POST["current_password"]) != $ilias->account->getPasswd())
	{
		$password_error=$lng->txt("passwd_wrong");
		//$ilias->raiseError($lng->txt("passwd_wrong"),$ilias->error_obj->MESSAGE);
	}

	// check new password
	else if ($_POST["desired_password"] != $_POST["retype_password"])
	{
		$password_error=$lng->txt("passwd_not_match");
		//$ilias->raiseError($lng->txt("passwd_not_match"),$ilias->error_obj->MESSAGE);
	}

	// validate password
	else if (!ilUtil::isPassword($_POST["desired_password"]))
	{
		$password_error=$lng->txt("passwd_invalid");
		//$ilias->raiseError($lng->txt("passwd_invalid"),$ilias->error_obj->MESSAGE);
	}

	else if ($_POST["current_password"] != "" and empty($password_error))
	{
		$ilias->account->updatePassword($_POST["current_password"], $_POST["desired_password"], $_POST["retype_password"]);

		/*if ($ilias->account->updatePassword($_POST["current_password"], $_POST["desired_password"], $_POST["retype_password"]))
		{
			sendInfo($lng->txt("msg_changes_ok"));

		}
		else
		{
			sendInfo($lng->txt("msg_failed"));

		}*/
	}
    }
}
// End of function change_password

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
//$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_profile.html");

// set locator
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_desktop"));
$tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_profile"));
$tpl->setVariable("LINK_ITEM", "usr_profile.php");
$tpl->parseCurrentBlock();

// catch feedback message
sendInfo();
// display infopanel if something happened
infoPanel();

// display tabs
include "./include/inc.personaldesktop_buttons.php";

// To display picture after Uploaded
//$tpl->setVariable("IMAGE_PATH","./".$webspace_dir."/usr_images/".$ilias->account->prefs["profile_image"]);

// if data are posted check on upload button
//if data are posted
if ($_GET["cmd"] == "save" and empty($_POST["usr_reload"]))
{
	$upload_error;

	if (workWithUserSetting("upload"))
	{
		// upload usr_image
		if (!empty($_POST["usr_upload"]))
		{
			upload_file();
		}
	
		// remove user image
		if (!empty($_POST["removePicture"]))
		{
			removePicture();
		}
	}

	// error content
	$password_error;

	//change password
	if (!empty($_POST["usr_change_password"]))
	{
		change_password();
	}

	//init checking var
	$form_valid = true;

	// testing by ratana ty:
	// if people check on check box it will
	// write some datata to table usr_pref
	// if check on Public Profile
	if (($_POST["chk_pub"])=="on")
	{
		$ilias->account->setPref("public_profile","y");
	}
	else
	{
		$ilias->account->setPref("public_profile","n");
	}

	// if check on Institute
	$val_array = array("institution", "department", "upload", "street",
		"zip", "city", "country", "phone_office", "phone_home", "phone_mobile",
		"fax", "email", "hobby", "matriculation");

	foreach($val_array as $key => $value)
	{
		if (($_POST["chk_".$value]) == "on")
		{
			$ilias->account->setPref("public_".$value,"y");
		}
		else
		{
			$ilias->account->setPref("public_".$value,"n");
		}
	}

    // check dynamically required fields
    foreach($settings as $key => $val)
    {
        if (substr($key,0,8) == "require_")
        {
            $require_keys[] = substr($key,8);
        }
    }

    foreach($require_keys as $key => $val)
    {
        // exclude required system and registration-only fields
        $system_fields = array("login", "default_role", "passwd", "passwd2");
        if (!in_array($val, $system_fields))
        {
					if (workWithUserSetting($val))
					{
            if (isset($settings["require_" . $val]) && $settings["require_" . $val])
            {
                if (empty($_POST["usr_" . $val]))
                {
                    sendInfo($lng->txt("fill_out_all_required_fields") . ": " . $lng->txt($val));
                    $form_valid = false;
                }
            }
					}
        }
    }

	if (workWithUserSetting("email"))
	{
		// check email adress
		if (!ilUtil::is_email($_POST["usr_email"]) and !empty($_POST["usr_email"]) and $form_valid)
		{
			sendInfo($lng->txt("email_not_valid"));
			$form_valid = false;
		}
	}

	//update user data (not saving!)
	if (workWithUserSetting("firstname"))
	{
	  $ilias->account->setFirstName(ilUtil::stripSlashes($_POST["usr_firstname"]));
	}
	if (workWithUserSetting("lastname"))
	{
	  $ilias->account->setLastName(ilUtil::stripSlashes($_POST["usr_lastname"]));
	}
	if (workWithUserSetting("gender"))
	{
		$ilias->account->setGender($_POST["usr_gender"]);
	}
	if (workWithUserSetting("title"))
	{
		$ilias->account->setUTitle(ilUtil::stripSlashes($_POST["usr_title"]));
	}
	$ilias->account->setFullname();
	// added for upload by ratana ty
	//$ilias->account->setFile($_POST["usr_file"]);
	if (workWithUserSetting("institution"))
	{
		$ilias->account->setInstitution(ilUtil::stripSlashes($_POST["usr_institution"]));
	}
	if (workWithUserSetting("department"))
	{
		$ilias->account->setDepartment(ilUtil::stripSlashes($_POST["usr_department"]));
	}
	if (workWithUserSetting("street"))
	{
		$ilias->account->setStreet(ilUtil::stripSlashes($_POST["usr_street"]));
	}
	if (workWithUserSetting("zipcode"))
	{
		$ilias->account->setZipcode(ilUtil::stripSlashes($_POST["usr_zipcode"]));
	}
	if (workWithUserSetting("city"))
	{
		$ilias->account->setCity(ilUtil::stripSlashes($_POST["usr_city"]));
	}
	if (workWithUserSetting("country"))
	{
		$ilias->account->setCountry(ilUtil::stripSlashes($_POST["usr_country"]));
	}
	if (workWithUserSetting("phone_office"))
	{
		$ilias->account->setPhoneOffice(ilUtil::stripSlashes($_POST["usr_phone_office"]));
	}
	if (workWithUserSetting("phone_home"))
	{
		$ilias->account->setPhoneHome(ilUtil::stripSlashes($_POST["usr_phone_home"]));
	}
	if (workWithUserSetting("phone_mobile"))
	{
		$ilias->account->setPhoneMobile(ilUtil::stripSlashes($_POST["usr_phone_mobile"]));
	}
	if (workWithUserSetting("fax"))
	{
		$ilias->account->setFax(ilUtil::stripSlashes($_POST["usr_fax"]));
	}
	if (workWithUserSetting("email"))
	{
		$ilias->account->setEmail(ilUtil::stripSlashes($_POST["usr_email"]));
	}
	if (workWithUserSetting("hobby"))
	{
		$ilias->account->setHobby(ilUtil::stripSlashes($_POST["usr_hobby"]));
	}
	if (workWithUserSetting("referral_comment"))
	{
		$ilias->account->setComment(ilUtil::stripSlashes($_POST["usr_referral_comment"]));
	}
	if (workWithUserSetting("matriculation"))
	{
		$ilias->account->setMatriculation(ilUtil::stripSlashes($_POST["usr_matriculation"]));
	}

	// everthing's ok. save form data
	if ($form_valid)
	{
		// init reload var. page should only be reloaded if skin or style were changed
		$reload = false;

		if (workWithUserSetting("skin_style"))
		{
			//set user skin and style
			if ($_POST["usr_skin_style"] != "")
			{
				$sknst = explode(":", $_POST["usr_skin_style"]);
	
				if ($ilias->account->getPref("style") != $sknst[1] ||
					$ilias->account->getPref("skin") != $sknst[0])
				{
					$ilias->account->setPref("skin", $sknst[0]);
					$ilias->account->setPref("style", $sknst[1]);
					$reload = true;
				}
			}
		}

		if (workWithUserSetting("language"))
		{
			// set user language
			$ilias->account->setLanguage($_POST["usr_language"]);

			// reload page if language was changed
			if ($_POST["usr_language"] != "" and $_POST["usr_language"] != $_SESSION['lang'])
			{
				$reload = true;
			}
		}
		if (workWithUserSetting("hits_per_page"))
		{
			// set user hits per page
			if ($_POST["hits_per_page"] != "")
			{
				$ilias->account->setPref("hits_per_page",$_POST["hits_per_page"]);
			}
		}

		// set show users online
		if (workWithUserSetting("show_users_online"))
		{
			$ilias->account->setPref("show_users_online", $_POST["show_users_online"]);
		}

		// save user data & object_data
		$ilias->account->setTitle($ilias->account->getFullname());
		$ilias->account->setDescription($ilias->account->getEmail());
		$ilias->account->update();

		// reload page only if skin or style were changed
		if ($reload)
		{
			// feedback
			sendInfo($lng->txt("saved_successfully"),true);
			$tpl->setVariable("RELOAD","<script language=\"Javascript\">\ntop.location.href = \"./start.php\";\n</script>\n");
		}
		else
		{
			// feedback
			if (!empty($password_error))
			{
				sendInfo($password_error,true);
			}
			elseif (!empty($upload_error))
			{
				sendInfo($upload_error,true);
			}
			else
			{
				sendInfo($lng->txt("saved_successfully"),true);
			}
			
			ilUtil::redirect("usr_profile.php");
		}

	}
}

if (userSettingVisible("language"))
{
	//get all languages
	$languages = $lng->getInstalledLanguages();
	
	// preselect previous chosen language otherwise saved language
	$selected_lang = (isset($_POST["usr_language"])) ? $_POST["usr_language"] : $ilias->account->getLanguage();
	
	//go through languages
	foreach($languages as $lang_key)
	{
		$tpl->setCurrentBlock("sel_lang");
		//$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
		$tpl->setVariable("LANG", ilLanguage::_lookupEntry($lang_key,"meta", "meta_l_".$lang_key));
		$tpl->setVariable("LANGSHORT", $lang_key);
	
		if ($selected_lang == $lang_key)
		{
			$tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
		}
	
		$tpl->parseCurrentBlock();
	}
}

// get all templates
include_once("classes/class.ilObjStyleSettings.php");
$templates = $styleDefinition->getAllTemplates();

if (userSettingVisible("skin_style"))
{
	foreach($templates as $template)
	{
		// get styles information of template
		$styleDef =& new ilStyleDefinition($template["id"]);
		$styleDef->startParsing();
		$styles = $styleDef->getStyles();
	
		foreach($styles as $style)
		{
			if (!ilObjStyleSettings::_lookupActivatedStyle($template["id"],$style["id"]))
			{
				continue;
			}

			$tpl->setCurrentBlock("selectskin");
	
			if ($ilias->account->skin == $template["id"] &&
				$ilias->account->prefs["style"] == $style["id"])
			{
				$tpl->setVariable("SKINSELECTED", "selected=\"selected\"");
			}
	
			$tpl->setVariable("SKINVALUE", $template["id"].":".$style["id"]);
			$tpl->setVariable("SKINOPTION", $styleDef->getTemplateName()." / ".$style["name"]);
			$tpl->parseCurrentBlock();
		}
	}
}

// hits per page
if (userSettingVisible("hits_per_page"))
{
	$hits_options = array(2,10,15,20,30,40,50,100,9999);

	foreach($hits_options as $hits_option)
	{
		$tpl->setCurrentBlock("selecthits");

		if ($ilias->account->prefs["hits_per_page"] == $hits_option)
		{
			$tpl->setVariable("HITSSELECTED", "selected=\"selected\"");
		}

		$tpl->setVariable("HITSVALUE", $hits_option);

		if ($hits_option == 9999)
		{
			$hits_option = $lng->txt("no_limit");
		}

		$tpl->setVariable("HITSOPTION", $hits_option);
		$tpl->parseCurrentBlock();
	}
}

// Users Online
if (userSettingVisible("show_users_online"))
{
	$users_online_options = array("y","associated","n");
	$selected_option = $ilias->account->prefs["show_users_online"];
	foreach($users_online_options as $an_option)
	{
		$tpl->setCurrentBlock("select_users_online");

		if ($selected_option == $an_option)
		{
			$tpl->setVariable("USERS_ONLINE_SELECTED", "selected=\"selected\"");
		}

		$tpl->setVariable("USERS_ONLINE_VALUE", $an_option);

		$tpl->setVariable("USERS_ONLINE_OPTION", $lng->txt("users_online_show_".$an_option));
		$tpl->parseCurrentBlock();
	}
}


if ($ilias->account->getAuthMode(true) == AUTH_LOCAL and userSettingVisible('password'))
{
	if($ilias->getSetting('usr_settings_disable_password'))
	{
        $tpl->setCurrentBlock("disabled_password");
        $tpl->setVariable("TXT_DISABLED_PASSWORD", $lng->txt("chg_password"));
        $tpl->setVariable("TXT_DISABLED_CURRENT_PASSWORD", $lng->txt("current_password"));
		$tpl->parseCurrentBlock();
	}
	elseif ($settings["passwd_auto_generate"] == 1)
	{
	    $passwd_list = ilUtil::generatePasswords(5);
     
        foreach ($passwd_list as $passwd)
        {
            $passwd_choice .= ilUtil::formRadioButton(0,"new_passwd",$passwd)." ".$passwd."<br/>";
        }

        $tpl->setCurrentBlock("select_password");
        $tpl->setVariable("TXT_CHANGE_PASSWORD", $lng->txt("chg_password"));
        $tpl->setVariable("TXT_CURRENT_PASSWORD", $lng->txt("current_password"));
        $tpl->setVariable("TXT_SELECT_PASSWORD", $lng->txt("select_password"));
        $tpl->setVariable("PASSWORD_CHOICE", $passwd_choice);
        $tpl->setVariable("TXT_NEW_LIST_PASSWORD", $lng->txt("new_list_password"));
        $tpl->parseCurrentBlock();
	}
	else
	{
        $tpl->setCurrentBlock("change_password");
        $tpl->setVariable("TXT_CHANGE_PASSWORD", $lng->txt("chg_password"));
        $tpl->setVariable("TXT_CURRENT_PW", $lng->txt("current_password"));
        $tpl->setVariable("TXT_DESIRED_PW", $lng->txt("desired_password"));
        $tpl->setVariable("TXT_RETYPE_PW", $lng->txt("retype_password"));
        $tpl->setVariable("CHANGE_PASSWORD",$lng->txt("chg_password"));
        $tpl->parseCurrentBlock();
	}
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("FORMACTION", "usr_profile.php?cmd=save");

$tpl->setVariable("HEADER", $lng->txt("personal_desktop"));
$tpl->setVariable("TXT_OF",strtolower($lng->txt("of")));
$tpl->setVariable("USR_FULLNAME",$ilias->account->getFullname());

$tpl->setVariable("TXT_USR_DATA", $lng->txt("userdata"));
$tpl->setVariable("TXT_NICKNAME", $lng->txt("username"));
$tpl->setVariable("TXT_PUBLIC_PROFILE", $lng->txt("public_profile"));

$data = array();
$data["fields"] = array();
$data["fields"]["gender"] = "";
$data["fields"]["firstname"] = "";
$data["fields"]["lastname"] = "";
$data["fields"]["title"] = "";
$data["fields"]["institution"] = "";
$data["fields"]["department"] = "";
$data["fields"]["street"] = "";
$data["fields"]["city"] = "";
$data["fields"]["zipcode"] = "";
$data["fields"]["country"] = "";
$data["fields"]["phone_office"] = "";
$data["fields"]["phone_home"] = "";
$data["fields"]["phone_mobile"] = "";
$data["fields"]["fax"] = "";
$data["fields"]["email"] = "";
$data["fields"]["hobby"] = "";
$data["fields"]["referral_comment"] = "";
$data["fields"]["matriculation"] = "";
$data["fields"]["create_date"] = "";
$data["fields"]["approve_date"] = "";
$data["fields"]["active"] = "";

$data["fields"]["default_role"] = $role;
// fill presets
foreach($data["fields"] as $key => $val)
{
	// note: general "title" is not as "title" for a person
	if ($key != "title")
	{
		$str = $lng->txt($key);
	}
	else
	{
		$str = $lng->txt("person_title");
	}

	// check to see if dynamically required
	if (isset($settings["require_" . $key]) && $settings["require_" . $key])
	{
				$str = $str . '<span class="asterisk">*</span>';
	}

	if (userSettingVisible("$key"))
	{
		$tpl->setVariable("TXT_".strtoupper($key), $str);
	}
}

if (userSettingVisible("gender"))
{
	$tpl->setVariable("TXT_GENDER_F",$lng->txt("gender_f"));
	$tpl->setVariable("TXT_GENDER_M",$lng->txt("gender_m"));
}

// todo
// capture image name including path ($archive_dir/$filename)
//$tpl->setVariable("IMAGE_PATH",$return_path);
//$tpl->setVariable("IMAGE_PATH",'$archive_dir."/".$filename');

if (userSettingVisible("upload"))
{
	$tpl->setVariable("TXT_UPLOAD",$lng->txt("personal_picture"));
	$webspace_dir = ilUtil::getWebspaceDir("output");
	$full_img = $ilias->account->getPref("profile_image");
	$last_dot = strrpos($full_img, ".");
	$small_img = substr($full_img, 0, $last_dot).
			"_small".substr($full_img, $last_dot, strlen($full_img) - $last_dot);
	$image_file = $webspace_dir."/usr_images/".$small_img;
	
	if (@is_file($image_file))
	{
			$tpl->setCurrentBlock("pers_image");
			$tpl->setVariable("IMG_PERSONAL", $image_file."?dummy=".rand(1,99999));
			$tpl->parseCurrentBlock();
			if (userSettingEnabled("upload"))
			{
				$tpl->setCurrentBlock("remove_pic");
				$tpl->setVariable("TXT_REMOVE_PIC",$lng->txt("remove_personal_picture"));
			}
			$tpl->parseCurrentBlock();
			$tpl->setCurrentBlock("content");
	}
	
	if (userSettingEnabled("upload"))
	{
		$tpl->setCurrentBlock("upload_pic");
		$tpl->setVariable("UPLOAD",$lng->txt("upload"));
	}
	$tpl->setVariable("TXT_FILE", $lng->txt("userfile"));
	$tpl->setVariable("USER_FILE", $lng->txt("user_file"));
}

// ilinc upload pic
if (userSettingVisible("upload") and $ilias->getSetting("ilinc_active"))
{
	$ilinc_data = $ilias->account->getiLincData();
		
	if ($ilinc_data["id"])
	{
		include_once ('ilinc/classes/class.ilnetucateXMLAPI.php');
		$ilincAPI = new ilnetucateXMLAPI();
		
		$ilincAPI->uploadPicture($ilias->account);
		$response = $ilincAPI->sendRequest("uploadPicture");
	
		// return URL to user's personal page
		$url = trim($response->data['url']['cdata']);

		$tpl->setCurrentBlock("ilinc_upload_pic");
		$tpl->setVariable("TXT_ILINC_UPLOAD", $lng->txt("ilinc_upload_pic_text"));
		$tpl->setVariable("ILINC_UPLOAD_LINK", $url);
		$tpl->setVariable("ILINC_UPLOAD_LINKTXT", $lng->txt("ilinc_upload_pic_linktext"));
		$tpl->parseCurrentBlock();
	}
}


if (userSettingVisible("language"))
{
	$tpl->setVariable("TXT_LANGUAGE",$lng->txt("language"));
}
if (userSettingVisible("show_users_online"))
{
	$tpl->setVariable("TXT_SHOW_USERS_ONLINE",$lng->txt("show_users_online"));
}
if (userSettingVisible("skin_style"))
{
	$tpl->setVariable("TXT_USR_SKIN_STYLE",$lng->txt("usr_skin_style"));
}
if (userSettingVisible("hits_per_page"))
{
	$tpl->setVariable("TXT_HITS_PER_PAGE",$lng->txt("usr_hits_per_page"));
}
if (userSettingVisible("show_users_online"))
{
	$tpl->setVariable("TXT_SHOW_USERS_ONLINE",$lng->txt("show_users_online"));
}
$tpl->setVariable("TXT_PERSONAL_DATA", $lng->txt("personal_data"));
$tpl->setVariable("TXT_SYSTEM_INFO", $lng->txt("system_information"));
$tpl->setVariable("TXT_CONTACT_DATA", $lng->txt("contact_data"));
if (userSettingVisible("matriculation"))
{
	$tpl->setVariable("TXT_OTHER", $lng->txt("user_profile_other"));
}
$tpl->setVariable("TXT_SETTINGS", $lng->txt("settings"));

//values
$tpl->setVariable("NICKNAME", ilUtil::prepareFormOutput($ilias->account->getLogin()));
//$tpl->setVariable("NICKNAME", ilUtil::prepareFormOutput($ilias->account->getLogin()." (#".$ilias->account->getId().")"));

if (userSettingVisible("firstname"))
{
	$tpl->setVariable("FIRSTNAME", ilUtil::prepareFormOutput($ilias->account->getFirstname()));
}
if (userSettingVisible("lastname"))
{
	$tpl->setVariable("LASTNAME", ilUtil::prepareFormOutput($ilias->account->getLastname()));
}

if (userSettingVisible("gender"))
{
	// gender selection
	$gender = strtoupper($ilias->account->getGender());
	
	if (!empty($gender))
	{
		$tpl->setVariable("BTN_GENDER_".$gender,"checked=\"checked\"");
	}
}

$tpl->setVariable("CREATE_DATE", $ilias->account->getCreateDate());
$tpl->setVariable("APPROVE_DATE", $ilias->account->getApproveDate());

if ($ilias->account->getActive())
{
    $tpl->setVariable("ACTIVE", "checked=\"checked\"");
}

if (userSettingVisible("title"))
{
	$tpl->setVariable("TITLE", ilUtil::prepareFormOutput($ilias->account->getUTitle()));
}
if (userSettingVisible("institution"))
{
	$tpl->setVariable("INSTITUTION", ilUtil::prepareFormOutput($ilias->account->getInstitution()));
}
if (userSettingVisible("department"))
{
	$tpl->setVariable("DEPARTMENT", ilUtil::prepareFormOutput($ilias->account->getDepartment()));
}
if (userSettingVisible("street"))
{
	$tpl->setVariable("STREET", ilUtil::prepareFormOutput($ilias->account->getStreet()));
}
if (userSettingVisible("zipcode"))
{
	$tpl->setVariable("ZIPCODE", ilUtil::prepareFormOutput($ilias->account->getZipcode()));
}
if (userSettingVisible("city"))
{
	$tpl->setVariable("CITY", ilUtil::prepareFormOutput($ilias->account->getCity()));
}
if (userSettingVisible("country"))
{
	$tpl->setVariable("COUNTRY", ilUtil::prepareFormOutput($ilias->account->getCountry()));
}
if (userSettingVisible("phone_office"))
{
	$tpl->setVariable("PHONE_OFFICE", ilUtil::prepareFormOutput($ilias->account->getPhoneOffice()));
}
if (userSettingVisible("phone_home"))
{
	$tpl->setVariable("PHONE_HOME", ilUtil::prepareFormOutput($ilias->account->getPhoneHome()));
}
if (userSettingVisible("phone_mobile"))
{
	$tpl->setVariable("PHONE_MOBILE", ilUtil::prepareFormOutput($ilias->account->getPhoneMobile()));
}
if (userSettingVisible("fax"))
{
	$tpl->setVariable("FAX", ilUtil::prepareFormOutput($ilias->account->getFax()));
}
if (userSettingVisible("email"))
{
	$tpl->setVariable("EMAIL", ilUtil::prepareFormOutput($ilias->account->getEmail()));
}
if (userSettingVisible("hobby"))
{
	$tpl->setVariable("HOBBY", ilUtil::prepareFormOutput($ilias->account->getHobby()));		// here
}
if (userSettingVisible("referral_comment"))
{
	$tpl->setVariable("REFERRAL_COMMENT", ilUtil::prepareFormOutput($ilias->account->getComment()));
}
if (userSettingVisible("matriculation"))
{
	$tpl->setVariable("MATRICULATION", ilUtil::prepareFormOutput($ilias->account->getMatriculation()));
}

// get assigned global roles (default roles)
$global_roles = $rbacreview->getGlobalRoles();

foreach($global_roles as $role_id)
{
	if (in_array($role_id,$_SESSION["RoleId"]))
	{
		$roleObj = $ilias->obj_factory->getInstanceByObjId($role_id);
		$role_names .= $roleObj->getTitle().", ";
		unset($roleObj);
	}
}

$tpl->setVariable("TXT_DEFAULT_ROLES",$lng->txt("default_roles"));
$tpl->setVariable("DEFAULT_ROLES",substr($role_names,0,-2));

$tpl->setVariable("TXT_REQUIRED_FIELDS",$lng->txt("required_field"));
//button
$tpl->setVariable("TXT_SAVE",$lng->txt("save"));
// addeding by ratana ty
if (userSettingEnabled("upload"))
{
	$tpl->setVariable("UPLOAD", $lng->txt("upload"));
}
// end adding
// Testing by ratana ty
// Show check if value in table usr_pref is y
//
if ($ilias->account->prefs["public_profile"]=="y")
{
	$tpl->setVariable("CHK_PUB","checked");
}
$val_array = array("institution", "department", "upload", "street",
	"zip", "city", "country", "phone_office", "phone_home", "phone_mobile",
	"fax", "email", "hobby", "matriculation", "show_users_online");
foreach($val_array as $key => $value)
{
	if (userSettingVisible("$value"))
	{
		if ($ilias->account->prefs["public_".$value] == "y")
		{
			$tpl->setVariable("CHK_".strtoupper($value), "checked");
		}
	}
}
// End of showing
// Testing by ratana ty


$profile_fields = array(
	"gender",
	"firstname",
	"lastname",
	"title",
	"upload",
	"institution",
	"department",
	"street",
	"city",
	"zipcode",
	"country",
	"phone_office",
	"phone_home",
	"phone_mobile",
	"fax",
	"email",
	"hobby",
	"matriculation",
	"referral_comment",
	"language",
	"skin_style",
	"hits_per_page",
	"show_users_online"
);
foreach ($profile_fields as $field)
{
	if (!$ilias->getSetting("usr_settings_hide_" . $field))
	{
		if ($ilias->getSetting("usr_settings_disable_" . $field))
		{
			$tpl->setVariable("DISABLED_" . strtoupper($field), " disabled=\"disabled\"");
		}
	}
}

$tpl->parseCurrentBlock();
$tpl->show();
?>
