<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

// catch hack attempts
if (!$ilias->getSetting("enable_registration"))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
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
	$tpl->setVariable("TXT_WELCOME", $lng->txt("welcome").", ".urldecode($_GET["fullname"])."!");
	$tpl->setVariable("TXT_REGISTERED", $lng->txt("txt_registered"));
	$tpl->setVariable("TXT_LOGIN", $lng->txt("login"));
	$tpl->setVariable("USERNAME", $_GET["username"]);
	$tpl->setVariable("PASSWORD", $_GET["password"]);
	
	$ilias->auth->logout();
	session_destroy();
	
	$tpl->show();
}	

function saveForm()
{
	global $ilias, $lng, $rbacadmin;

	// check required fields
	if (empty($_POST["Fobject"]["firstname"]) or empty($_POST["Fobject"]["lastname"])
		or empty($_POST["Fobject"]["login"]) or empty($_POST["Fobject"]["email"])
		or empty($_POST["Fobject"]["passwd"]) or empty($_POST["Fobject"]["passwd2"]))
	{
		$ilias->raiseError($lng->txt("fill_out_all_required_fields"),$ilias->error_obj->MESSAGE);
	}

	// check loginname
	if (loginExists($_POST["Fobject"]["login"]))
	{
		$ilias->raiseError($lng->txt("login_exists"),$ilias->error_obj->MESSAGE);
	}

	// check passwords
	if ($_POST["Fobject"]["passwd"] != $_POST["Fobject"]["passwd2"])
	{
		$ilias->raiseError($lng->txt("passwd_not_match"),$ilias->error_obj->MESSAGE);
	}

	// validate password
	if (!ilUtil::is_password($_POST["Fobject"]["passwd"]))
	{
		$ilias->raiseError($lng->txt("passwd_invalid"),$ilias->error_obj->MESSAGE);
	}

	// validate email
	if (!ilUtil::is_email($_POST["Fobject"]["email"]))
	{
		$ilias->raiseError($lng->txt("email_not_valid"),$ilias->error_obj->MESSAGE);
	}

	// TODO: check if login or passwd already exists
	// TODO: check length of login and passwd

	// checks passed. save user
	$userObj = new ilObjUser();
	$userObj->assignData($_POST["Fobject"]);
	$userObj->setTitle($userObj->getFullname());
	$userObj->setDescription($userObj->getEmail());
	$userObj->create();

	//insert user data in table user_data
	$userObj->saveAsNew();

	// setup user preferences
	$userObj->setLanguage($_POST["usr_language"]);
	$userObj->writePrefs();

	//set role entries
	$rbacadmin->assignUser($_POST["Fobject"]["default_role"],$userObj->getId(),true);

	// CREATE ENTRIES FOR MAIL BOX
	include_once ("classes/class.ilMailbox.php");
	$mbox = new ilMailbox($userObj->getId());
	$mbox->createDefaultFolder();

	include_once "classes/class.ilFormatMail.php";
	$fmail = new ilFormatMail($userObj->getId());
	$fmail->createMailOptionsEntry();

	// create personal bookmark folder tree
	include_once "classes/class.ilBookmarkFolder.php";
	$bmf = new ilBookmarkFolder(0, $userObj->getId());
	$bmf->createNewBookmarkTree();

	header("location: register.php?cmd=login&username=".$_POST["Fobject"]["login"]."&password=".$_POST["Fobject"]["passwd"]."&fullname=".urlencode($userObj->getFullname()));
	exit();
}


function displayForm ()
{
	global $tpl,$ilias,$lng;

	//instantiate login template
	$tpl->addBlockFile("CONTENT", "content", "tpl.usr_registration.html");

	// for gender selection. don't change this
	$gender_arr = array('m' => "salutation_m",'f'=> "salutation_f");

	// gender selection
	$gender = ilUtil::formSelect($Fobject["gender"],"Fobject[gender]",array('m' => "salutation_m",'f'=> "salutation_f"));
	
	// role selection (only those roles marked with allow_register)
	$q = "SELECT * FROM role_data ".
		 "LEFT JOIN object_data ON object_data.obj_id = role_data.role_id ".
		 "WHERE allow_register = 1";
	$r = $ilias->db->query($q);
	
	if ($r->numRows() > 0)
	{
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$role_list[$row->obj_id] = fetchObjectData($row);
		}
	}
	
	foreach ($role_list as $obj_data)
	{
		$rol[$obj_data["obj_id"]] = $obj_data["title"];
	}

	$role = ilUtil::formSelectWoTranslation($Fobject["default_role"],"Fobject[default_role]",$rol);
	
	$data = array();
	$data["fields"] = array();
	$data["fields"]["login"] = "";
	$data["fields"]["passwd"] = "";
	$data["fields"]["passwd2"] = "";
	$data["fields"]["title"] = "";
	$data["fields"]["gender"] = $gender;
	$data["fields"]["firstname"] = "";
	$data["fields"]["lastname"] = "";
	$data["fields"]["institution"] = "";
	$data["fields"]["street"] = "";
	$data["fields"]["city"] = "";
	$data["fields"]["zipcode"] = "";
	$data["fields"]["country"] = "";
	$data["fields"]["phone"] = "";
	$data["fields"]["email"] = "";
	$data["fields"]["hobby"] = "";
	$data["fields"]["default_role"] = $role;
	
	foreach ($data["fields"] as $key => $val)
	{
		$tpl->setVariable("TXT_".strtoupper($key), $lng->txt($key));
		$tpl->setVariable(strtoupper($key), $val);
	}
	
	$tpl->setVariable("FORMACTION", "register.php?cmd=save");
	$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	$tpl->setVariable("TXT_REQUIRED_FIELDS", $lng->txt("required_field"));
	$tpl->setVariable("TXT_LOGIN_DATA", $lng->txt("login_data"));
	$tpl->setVariable("TXT_PERSONAL_DATA", $lng->txt("personal_data"));
	$tpl->setVariable("TXT_CONTACT_DATA", $lng->txt("contact_data"));
	$tpl->setVariable("TXT_SETTINGS", $lng->txt("settings"));
	$tpl->setVariable("TXT_PASSWD2", $lng->txt("retype_password"));
	$tpl->setVariable("TXT_LANGUAGE",$lng->txt("language"));

	// language selection
	$languages = $lng->getInstalledLanguages();
	
	foreach ($languages as $lang_key)
	{
		$tpl->setCurrentBlock("language_selection");
		$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
		$tpl->setVariable("LANGSHORT", $lang_key);
	
		if ($ilias->getSetting("language") == $lang_key)
		{
			$tpl->setVariable("SELECTED_LANG", "selected=\"selected\"");
		}
	
		$tpl->parseCurrentBlock();
	} // END language selection
	
	// FILL SAVED VALUES IN CASE OF ERROR
	$tpl->setVariable("LOGIN",$_SESSION["error_post_vars"]["Fobject"]["login"]);
	$tpl->setVariable("FIRSTNAME",$_SESSION["error_post_vars"]["Fobject"]["firstname"]);
	$tpl->setVariable("LASTNAME",$_SESSION["error_post_vars"]["Fobject"]["lastname"]);
	$tpl->setVariable("TITLE",$_SESSION["error_post_vars"]["Fobject"]["title"]);
	$tpl->setVariable("INSTITUTION",$_SESSION["error_post_vars"]["Fobject"]["institution"]);
	$tpl->setVariable("STREET",$_SESSION["error_post_vars"]["Fobject"]["street"]);
	$tpl->setVariable("CITY",$_SESSION["error_post_vars"]["Fobject"]["city"]);
	$tpl->setVariable("ZIPCODE",$_SESSION["error_post_vars"]["Fobject"]["zipcode"]);
	$tpl->setVariable("COUNTRY",$_SESSION["error_post_vars"]["Fobject"]["country"]);
	$tpl->setVariable("PHONE",$_SESSION["error_post_vars"]["Fobject"]["phone"]);
	$tpl->setVariable("EMAIL",$_SESSION["error_post_vars"]["Fobject"]["email"]);
	$tpl->setVariable("HOBBY",$_SESSION["error_post_vars"]["Fobject"]["hobby"]);
	
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("registration"));
	$tpl->setVariable("TXT_REGISTER_INFO", $lng->txt("register_info"));
	
	$tpl->show();
}
?>