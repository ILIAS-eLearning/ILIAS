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
* registration form for new users
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/

require_once "include/inc.header.php";
require_once "classes/class.ilUserAgreement.php";

// catch hack attempts
if (!$ilias->getSetting("enable_registration"))
{
    if (empty($_SESSION["AccountId"]) and $_SESSION["AccountId"] !== false)
    {
        $ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
    }
}

switch ($_GET["cmd"])
{
	case "save":
		saveForm();
		break;

	case "login":
		loginPage();
		break;

	default:
		displayForm();
		break;
}

function loginPage()
{
	global $tpl,$ilias,$lng;

	$tpl->addBlockFile("CONTENT", "content", "tpl.usr_registered.html");
	$tpl->setVariable("FORMACTION","login.php");
	$tpl->setVariable("TARGET","target=\"_parent\"");
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("registration"));
	$tpl->setVariable("TXT_WELCOME", $lng->txt("welcome").", ".urldecode(ilUtil::stripSlashes($_GET["name"]))."!");
    if ($ilias->getSetting("auto_registration"))
    {
        $tpl->setVariable("TXT_REGISTERED", $lng->txt("txt_registered"));
    }
    else
    {
        $tpl->setVariable("TXT_REGISTERED", $lng->txt("txt_submitted"));
    }
	$tpl->setVariable("TXT_LOGIN", $lng->txt("login_to_ilias"));
	$tpl->setVariable("USERNAME", base64_decode($_GET["user"]));
	$tpl->setVariable("PASSWORD", base64_decode($_GET["pass"]));

	$ilias->auth->logout();
	session_destroy();

	$tpl->show();
}

function saveForm()
{
	global $tpl, $ilias, $lng, $rbacadmin, $ilDB, $ilErr;

    //load ILIAS settings
    $settings = $ilias->getAllSettings();

	//$tpl->addBlockFile("CONTENT", "content", "tpl.group_basic.html");
	//sendInfo();
	//InfoPanel();

	//check, whether user-agreement has been accepted
	if (! ($_POST["status"]=="accepted") )
	{
		$ilias->raiseError($lng->txt("force_accept_usr_agreement"),$ilias->error_obj->MESSAGE);
    }

    // check dynamically required fields
    foreach ($settings as $key => $val)
    {
        if (substr($key,0,8) == "require_")
        {
            if ($settings["passwd_auto_generate"] == 1 and ($key == "require_passwd" or $key == "require_passwd2"))
            {
                continue;
            }
            
            $require_keys[] = substr($key,8);
        }
    }

    foreach ($require_keys as $key => $val)
    {
        if (isset($settings["require_" . $val]) && $settings["require_" . $val])
        {
            if (empty($_POST["Fobject"][$val]))
            {
                $ilias->raiseError($lng->txt("fill_out_all_required_fields") . ": " . $lng->txt($val),$ilias->error_obj->MESSAGE);
            }
        }
    }

	if(!checkUserDefinedRequiredFields())
	{
		$ilias->raiseError($lng->txt("fill_out_all_required_fields"),$ilias->error_obj->MESSAGE);
	}
		

    // validate username
	if (!ilUtil::isLogin($_POST["Fobject"]["login"]))
	{
		$ilias->raiseError($lng->txt("login_invalid"),$ilias->error_obj->MESSAGE);
	}

	// check loginname
	if (loginExists($_POST["Fobject"]["login"]))
	{
		$ilias->raiseError($lng->txt("login_exists"),$ilias->error_obj->MESSAGE);
	}

    if ($settings["passwd_auto_generate"] != 1)
    {
        // check passwords
        if ($_POST["Fobject"]["passwd"] != $_POST["Fobject"]["passwd2"])
        {
            $ilias->raiseError($lng->txt("passwd_not_match"),$ilias->error_obj->MESSAGE);
        }

        // validate password
        if (!ilUtil::isPassword($_POST["Fobject"]["passwd"]))
        {
            $ilias->raiseError($lng->txt("passwd_invalid"),$ilias->error_obj->MESSAGE);
        }
    }
    else
    {    
    	$passwd = ilUtil::generatePasswords(1);
    	$_POST["Fobject"]["passwd"] = $passwd[0];
    }
	// The password type is not passed in the post data. Therefore we
	// append it here manually.
	require_once "classes/class.ilObjUser.php";
    $_POST["Fobject"]["passwd_type"] = IL_PASSWD_PLAIN;

	// validate email
	if (!ilUtil::is_email($_POST["Fobject"]["email"]))
	{
		$ilias->raiseError($lng->txt("email_not_valid"),$ilias->error_obj->MESSAGE);
	}

	// validate role
	include_once("classes/class.ilObjRole.php");
	if (!ilObjRole::_lookupAllowRegister($_POST["Fobject"]["default_role"]))
	{
		$ilias->raiseError("Invalid role selection in registration: ".
			ilObject::_lookupTitle($_POST["Fobject"]["default_role"])." [".$_POST["Fobject"]["default_role"]."]".
			", IP: ".$_SERVER["REMOTE_ADDR"],$ilias->error_obj->FATAL);
	}
	
	// get auth mode of role
	$auth_mode = ilObjRole::_getAuthMode($_POST["Fobject"]["default_role"]);
	$_POST["Fobject"]['auth_mode'] = $auth_mode;
	
	// validate authentication, if mode != LOCAL
	// to do: if auth is taken out of ilias class, this should be
	// implemented better
	// to do: also needed for ldap
	if ($auth_mode == "radius")
	{
		$_POST['username'] = $_POST["Fobject"]["login"];
		$_POST['password'] = $_POST["Fobject"]["passwd"];
		include_once('classes/class.ilRADIUSAuthentication.php');
		$radius_servers = ilRADIUSAuthentication::_getServers($ilDB);
		$settings = $ilias->getAllSettings();
		
		foreach ($radius_servers as $radius_server)
		{
			$rad_params['servers'][] = array($radius_server,$settings["radius_port"],$settings["radius_shared_secret"]);
		}
		$auth = new Auth("RADIUS", $rad_params,"",false);
		$auth->start();
		$err = $ilErr->getLastError();
		if (!$auth->getAuth())
		{
			$add = (!is_object($err))
				? ""
				: "<br>".$err->getMessage();
			$ilias->raiseError($lng->txt("could_not_verify_account").
				$add, $ilErr->MESSAGE);
		}
	}

	// TODO: check if login or passwd already exists
	// TODO: check length of login and passwd

	// checks passed. save user

	$userObj = new ilObjUser();
	$userObj->assignData($_POST["Fobject"]);
	$userObj->setTitle($userObj->getFullname());
	$userObj->setDescription($userObj->getEmail());

	// Time limit
	$userObj->setTimeLimitOwner(7);
	$userObj->setTimeLimitUnlimited(1);
	$userObj->setTimeLimitFrom(time());
	$userObj->setTimeLimitUntil(time());

	$userObj->setUserDefinedData($_POST['udf']);

	$userObj->create();

    if (isset($settings["auto_registration"]) && ($settings["auto_registration"] == 1))
    {
        $userObj->setActive(1, 6);
    }
    else
    {
        $userObj->setActive(0, 0);
    }

    $userObj->updateOwner();

	//insert user data in table user_data
	$userObj->saveAsNew();
	
	// store acceptance of user agreement
	$userObj->writeAccepted();

	// setup user preferences
	$userObj->setLanguage($_POST["Fobject"]["language"]);
	$userObj->setPref("hits_per_page", $ilias->getSetting("hits_per_page"));
	$userObj->setPref("show_users_online", $ilias->getSetting("show_users_online"));
	$userObj->writePrefs();

	//set role entries
	$rbacadmin->assignUser($_POST["Fobject"]["default_role"],$userObj->getId(),true);

	// CREATE ENTRIES FOR MAIL BOX
	/* moved folder creation to ObjUser->saveAsNew
	include_once ("classes/class.ilMailbox.php");
	$mbox = new ilMailbox($userObj->getId());
	$mbox->createDefaultFolder();

	include_once "classes/class.ilMailOptions.php";
	$mail_options = new ilMailOptions($userObj->getId());
	$mail_options->createMailOptionsEntry();

	// create personal bookmark folder tree
	include_once "classes/class.ilBookmarkFolder.php";
	$bmf = new ilBookmarkFolder(0, $userObj->getId());
	$bmf->createNewBookmarkTree();*/

    if (!$ilias->getSetting("auto_registration"))
    {
        $approve_recipient = $ilias->getSetting("approve_recipient");
        if (empty($approve_recipient))
        {
            $approve_recipient = $userObj->getLoginByUserId(6);
        }

        include_once "classes/class.ilFormatMail.php";

        $umail = new ilFormatMail($userObj->getId());

        // mail subject
        $subject = $lng->txt("client_id") . " " . $ilias->client_id . ": " . $lng->txt("usr_new");

        // mail body
        $body = $lng->txt("login").": ".$userObj->getLogin()."\n\r".
                $lng->txt("passwd").": ".$_POST["Fobject"]["passwd"]."\n\r".
                $lng->txt("title").": ".$userObj->getTitle()."\n\r".
                $lng->txt("gender").": ".$userObj->getGender()."\n\r".
                $lng->txt("firstname").": ".$userObj->getFirstname()."\n\r".
                $lng->txt("lastname").": ".$userObj->getLastname()."\n\r".
                $lng->txt("institution").": ".$userObj->getInstitution()."\n\r".
                $lng->txt("department").": ".$userObj->getDepartment()."\n\r".
                $lng->txt("street").": ".$userObj->getStreet()."\n\r".
                $lng->txt("city").": ".$userObj->getCity()."\n\r".
                $lng->txt("zipcode").": ".$userObj->getZipcode()."\n\r".
                $lng->txt("country").": ".$userObj->getCountry()."\n\r".
                $lng->txt("phone_office").": ".$userObj->getPhoneOffice()."\n\r".
                $lng->txt("phone_home").": ".$userObj->getPhoneHome()."\n\r".
                $lng->txt("phone_mobile").": ".$userObj->getPhoneMobile()."\n\r".
                $lng->txt("fax").": ".$userObj->getFax()."\n\r".
                $lng->txt("email").": ".$userObj->getEmail()."\n\r".
                $lng->txt("hobby").": ".$userObj->getHobby()."\n\r".
                $lng->txt("referral_comment").": ".$userObj->getComment()."\n\r".
                $lng->txt("matriculation").": ".$userObj->getMatriculation()."\n\r".
                $lng->txt("create_date").": ".$userObj->getCreateDate()."\n\r".
                $lng->txt("default_role").": ".$_POST["Fobject"]["default_role"]."\n\r";

        $error_message = $umail->sendMail($approve_recipient,"","",$subject,$body,array(),array("normal"));
    }

    if ($settings["passwd_auto_generate"] == 1)
    {
        include_once "classes/class.ilMimeMail.php";

		$mmail = new ilMimeMail();
		$mmail->autoCheck(false);
		$mmail->From($settings["admin_email"]);
		$mmail->To($userObj->getEmail());

        // mail subject
        $subject = $lng->txt("reg_mail_subject");

		// mail body
		$body = $lng->txt("reg_mail_body_salutation")." ".$userObj->getFullname().",\n\r".
				$lng->txt("reg_mail_body_welcome")."\n\r".
				$lng->txt("reg_mail_body_text1")."\n\r".
				$lng->txt("reg_mail_body_text2")."\n\r".
				ILIAS_HTTP_PATH."login.php?client_id=".$ilias->client_id."\n\r".
				$lng->txt("login").": ".$userObj->getLogin()."\n\r".
				$lng->txt("passwd").": ".$_POST["Fobject"]["passwd"]."\n\r\n\r".
				$lng->txt("reg_mail_body_text3")."\n\r".
				$lng->txt("title").": ".$userObj->getTitle()."\n\r".
				$lng->txt("gender").": ".$userObj->getGender()."\n\r".
				$lng->txt("firstname").": ".$userObj->getFirstname()."\n\r".
				$lng->txt("lastname").": ".$userObj->getLastname()."\n\r".
				$lng->txt("institution").": ".$userObj->getInstitution()."\n\r".
				$lng->txt("department").": ".$userObj->getDepartment()."\n\r".
				$lng->txt("street").": ".$userObj->getStreet()."\n\r".
				$lng->txt("city").": ".$userObj->getCity()."\n\r".
				$lng->txt("zipcode").": ".$userObj->getZipcode()."\n\r".
				$lng->txt("country").": ".$userObj->getCountry()."\n\r".
				$lng->txt("phone_office").": ".$userObj->getPhoneOffice()."\n\r".
				$lng->txt("phone_home").": ".$userObj->getPhoneHome()."\n\r".
				$lng->txt("phone_mobile").": ".$userObj->getPhoneMobile()."\n\r".
				$lng->txt("fax").": ".$userObj->getFax()."\n\r".
				$lng->txt("email").": ".$userObj->getEmail()."\n\r".
				$lng->txt("hobby").": ".$userObj->getHobby()."\n\r".
				$lng->txt("referral_comment").": ".$userObj->getComment()."\n\r".
				$lng->txt("create_date").": ".$userObj->getCreateDate()."\n\r".
				$lng->txt("default_role").": ".$_POST["Fobject"]["default_role"]."\n\r";

		$mmail->Subject($subject);
		$mmail->Body($body);
		$mmail->Send();
    }

	ilUtil::redirect("register.php?lang=".$_GET["lang"]."&cmd=login&user=".
					 base64_encode($_POST["Fobject"]["login"])."&pass=".
					 base64_encode($_POST["Fobject"]["passwd"])."&name=".urlencode(ilUtil::stripSlashes($userObj->getFullname())));
}


function displayForm()
{
	global $tpl,$ilias,$lng,$ObjDefinition;

    //load ILIAS settings
    $settings = $ilias->getAllSettings();

	// load login template
	$tpl->addBlockFile("CONTENT", "content", "tpl.usr_registration.html");
	$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

	//sendInfo();
	//infoPanel();
	// role selection (only those roles marked with allow_register)
	// TODO put query in a function
	include_once("classes/class.ilObjRole.php");
	$reg_roles = ilObjRole::_lookupRegisterAllowed();

	$rol = array();
	foreach ($reg_roles as $role)
	{
		$rol[$role["id"]] = $role["title"];
	}

	$role = ilUtil::formSelect($_SESSION["error_post_vars"]["Fobject"]["default_role"],"Fobject[default_role]",$rol,false,true);

	$data = array();
	$data["fields"] = array();
	$data["fields"]["login"] = "";

    if ($settings["passwd_auto_generate"] != 1)
    {
        $data["fields"]["passwd"] = "";
        $data["fields"]["passwd2"] = "";
    }
    
	$data["fields"]["title"] = "";
	$data["fields"]["gender"] = "";
	$data["fields"]["firstname"] = "";
	$data["fields"]["lastname"] = "";
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
	$data["fields"]["default_role"] = $role;

	// fill presets
	foreach ($data["fields"] as $key => $val)
	{
		$str = $lng->txt($key);
		if ($key == "title")
		{
			$str = $lng->txt("person_title");
		}
		
		if (!in_array($key, array("default_role", "login", "passwd", "passwd2",
			"firstname", "lastname", "gender")))
		{
			 if ($settings["usr_settings_hide_".$key] != 1)
			 {
				 $tpl->setCurrentBlock($key."_section");
//echo "<br>+section+".$key."_section"."+";
			 }
			 else
			 {
				 continue;
			 }
		}

        // check to see if dynamically required
        if (isset($settings["require_" . $key]) && $settings["require_" . $key])
        {
            $str = $str . '<span class="asterisk">*</span>';
        }

		$tpl->setVariable("TXT_".strtoupper($key), $str);
//echo ".TXT_".strtoupper($key).".".$str.".";

		if ($key == "default_role")
		{
			$tpl->setVariable(strtoupper($key), $val);
		}
		else
		{
			if (isset($_SESSION["error_post_vars"]["Fobject"]))
			{
				$val = $_SESSION["error_post_vars"]["Fobject"][$key];
			}
			$tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val,true));
//echo "-".strtoupper($key)."-".ilUtil::prepareFormOutput($val,true)."-";
		}
		
		if (!in_array($key, array("default_role", "login", "passwd", "passwd2",
			"firstname", "lastname", "gender")))
		{
			$tpl->parseCurrentBlock();
//echo ":parseCurrentBlock";
		}
	}


    if ($settings["passwd_auto_generate"] != 1)
    {
        // text label for passwd2 is nonstandard
        $str = $lng->txt("retype_password");
        if (isset($settings["require_passwd2"]) && $settings["require_passwd2"])
        {
            $str = $str . '<span class="asterisk">*</span>';
        }

        $tpl->setVariable("TXT_PASSWD2", $str);
    }
    else
    {
        $tpl->setVariable("TXT_PASSWD_SELECT", $lng->txt("passwd"));
        $tpl->setVariable("TXT_PASSWD_VIA_MAIL", $lng->txt("reg_passwd_via_mail"));
    }
//$tpl->show(); exit;
	$tpl->setVariable("FORMACTION", "register.php?cmd=save&lang=".$_GET["lang"]);
	$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	$tpl->setVariable("TXT_REQUIRED_FIELDS", $lng->txt("required_field"));
	$tpl->setVariable("TXT_LOGIN_DATA", $lng->txt("login_data"));
	$tpl->setVariable("TXT_PERSONAL_DATA", $lng->txt("personal_data"));
	$tpl->setVariable("TXT_CONTACT_DATA", $lng->txt("contact_data"));
	$tpl->setVariable("TXT_SETTINGS", $lng->txt("settings"));
	$tpl->setVariable("TXT_OTHER", $lng->txt("user_profile_other"));
	$tpl->setVariable("TXT_LANGUAGE",$lng->txt("language"));
	$tpl->setVariable("TXT_GENDER_F",$lng->txt("gender_f"));
	$tpl->setVariable("TXT_GENDER_M",$lng->txt("gender_m"));
	$tpl->setVariable("TXT_OK",$lng->txt("ok"));
	$tpl->setVariable("TXT_CHOOSE_LANGUAGE", $lng->txt("choose_language"));

	// language selection
	$languages = $lng->getInstalledLanguages();
	
		$count = (int) round(count($languages) / 2);
		$num = 1;
		
		foreach ($languages as $lang_key)
		{
			/*
			if ($num === $count)
			{
				$tpl->touchBlock("lng_new_row");
			}
			*/

			$tpl->setCurrentBlock("languages");
			$tpl->setVariable("LINK_LANG", "./register.php?lang=".$lang_key);
			$tpl->setVariable("LANG_NAME",
				ilLanguage::_lookupEntry($lang_key, "meta", "meta_l_".$lang_key));
			$tpl->setVariable("LANG_ICON", $lang_key);
			$tpl->setVariable("BORDER", 0);
			$tpl->setVariable("VSPACE", 0);
			$tpl->parseCurrentBlock();

			$num++;
		}
		
		/*
		if (count($languages) % 2)
		{
			$tpl->touchBlock("lng_empty_cell");
		}
		*/

	// preselect previous chosen language otherwise default language
	$selected_lang = (isset($_SESSION["error_post_vars"]["Fobject"]["language"])) ? 
		$_SESSION["error_post_vars"]["Fobject"]["language"] : $lng->lang_key;

	foreach ($languages as $lang_key)
	{
		$tpl->setCurrentBlock("language_selection");
		$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
		$tpl->setVariable("LANGSHORT", $lang_key);

		if ($selected_lang == $lang_key)
		{
			$tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
		}

		$tpl->parseCurrentBlock();
	} // END language selection

	// FILL SAVED VALUES IN CASE OF ERROR
	if (isset($_SESSION["error_post_vars"]["Fobject"]))
	{
		// gender selection
		$gender = strtoupper($_SESSION["error_post_vars"]["Fobject"]["gender"]);

		if (!empty($gender))
		{
			$tpl->setVariable("BTN_GENDER_".$gender,"checked=\"checked\"");
		}
	}
	
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("registration"));
	$tpl->setVariable("TXT_PAGETITLE", "ILIAS3 - ".$lng->txt("registration"));
	$tpl->setVariable("TXT_REGISTER_INFO", $lng->txt("register_info"));
	$tpl->setVariable("AGREEMENT", ilUserAgreement::_getText());
	$tpl->setVariable("ACCEPT_CHECKBOX", ilUtil::formCheckbox(0, "status", "accepted"));
    $tpl->setVariable("ACCEPT_AGREEMENT", $lng->txt("accept_usr_agreement") . '<span class="asterisk">*</span>');

	showUserDefinedFields();

	$tpl->show();

}
function showUserDefinedFields()
{
	global $tpl;

	include_once './classes/class.ilUserDefinedFields.php';
	$user_defined_fields = new ilUserDefinedFields();

	#$user_defined_data = $ilUser->getUserDefinedData();
	foreach($user_defined_fields->getVisibleDefinitions() as $field_id => $definition)
	{
		if($definition['field_type'] == UDF_TYPE_TEXT)
		{
			$old = isset($_SESSION["error_post_vars"]["udf"][$field_id]) ?
				$_SESSION["error_post_vars"]["udf"][$field_id] : '';


			$tpl->setCurrentBlock("field_text");
			$tpl->setVariable("FIELD_NAME",'udf['.$definition['field_id'].']');
			$tpl->setVariable("FIELD_VALUE",ilUtil::prepareFormOutput($old));
			if(!$definition['changeable'])
			{
				$tpl->setVariable("DISABLED_FIELD",'disabled=\"disabled\"');
			}
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("field_select");
			$tpl->setVariable("SELECT_BOX",ilUtil::formSelect($old,
															  'udf['.$definition['field_id'].']',
															  $user_defined_fields->fieldValuesToSelectArray(
																  $definition['field_values']),
															  false,
															  true));
			$tpl->parseCurrentBlock();
		}
		$tpl->setCurrentBlock("user_defined");

		if($definition['required'])
		{
			$name = $definition['field_name']."<span class=\"asterisk\">*</span>";
		}
		else
		{
			$name = $definition['field_name'];
		}
		$tpl->setVariable("TXT_FIELD_NAME",$name);
		$tpl->parseCurrentBlock();
	}
	return true;
}
function checkUserDefinedRequiredFields()
{
	include_once './classes/class.ilUserDefinedFields.php';
	$user_defined_fields = new ilUserDefinedFields();

	foreach($user_defined_fields->getVisibleDefinitions() as $field_id => $definition)
	{
		if($definition['required'] and !strlen($_POST['udf'][$field_id]))
		{
			return false;
		}
	}
	return true;
}

?>