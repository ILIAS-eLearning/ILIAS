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
if (!$ilias->getSetting("enable_registration") or AUTH_CURRENT != AUTH_LOCAL)
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
	$tpl->setVariable("TXT_REGISTERED", $lng->txt("txt_registered"));
	$tpl->setVariable("TXT_LOGIN", $lng->txt("login"));
	$tpl->setVariable("USERNAME", base64_decode($_GET["user"]));
	$tpl->setVariable("PASSWORD", base64_decode($_GET["pass"]));

	$ilias->auth->logout();
	session_destroy();

	$tpl->show();
}

function saveForm()
{
	global $tpl, $ilias, $lng, $rbacadmin;

	//$tpl->addBlockFile("CONTENT", "content", "tpl.group_basic.html");
	//sendInfo();
	//InfoPanel();

	//check, whether user-agreement has been accepted
	if (! ($_POST["status"]=="accepted") )
	{
		$ilias->raiseError($lng->txt("force_accept_usr_agreement"),$ilias->error_obj->MESSAGE);

	}

	// check required fields
	if (empty($_POST["Fobject"]["firstname"]) or empty($_POST["Fobject"]["lastname"])
		or empty($_POST["Fobject"]["login"]) or empty($_POST["Fobject"]["email"])
		or empty($_POST["Fobject"]["passwd"]) or empty($_POST["Fobject"]["passwd2"])
		or empty($_POST["Fobject"]["gender"]))
	{
		$ilias->raiseError($lng->txt("fill_out_all_required_fields"),$ilias->error_obj->MESSAGE);
	}

	// validate useername
	if (!ilUtil::isLogin($_POST["Fobject"]["login"]))
	{
		$ilias->raiseError($lng->txt("login_invalid"),$ilias->error_obj->MESSAGE);
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
	if (!ilUtil::isPassword($_POST["Fobject"]["passwd"]))
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

	// Time limit
	$userObj->setTimeLimitOwner(7);
	$userObj->setTimeLimitUnlimited(1);
	$userObj->setTimeLimitFrom(time());
	$userObj->setTimeLimitUntil(time());

	$userObj->create();

	//insert user data in table user_data
	$userObj->saveAsNew();

	// setup user preferences
	$userObj->setLanguage($_POST["Fobject"]["language"]);
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

	ilUtil::redirect("register.php?lang=".$_GET["lang"]."&cmd=login&user=".base64_encode($_POST["Fobject"]["login"])."&pass=".base64_encode($_POST["Fobject"]["passwd"])."&name=".urlencode(ilUtil::stripSlashes($userObj->getFullname())));
}


function displayForm ()
{
	global $tpl,$ilias,$lng,$ObjDefinition;

	// load login template
	$tpl->addBlockFile("CONTENT", "content", "tpl.usr_registration.html");
	$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

	//sendInfo();
	//infoPanel();
	// role selection (only those roles marked with allow_register)
	// TODO put query in a function
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

	$role = ilUtil::formSelect($_SESSION["error_post_vars"]["Fobject"]["default_role"],"Fobject[default_role]",$rol,false,true);

	$data = array();
	$data["fields"] = array();
	$data["fields"]["login"] = "";
	$data["fields"]["passwd"] = "";
	$data["fields"]["passwd2"] = "";
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
	$data["fields"]["default_role"] = $role;

	// fill presets
	foreach ($data["fields"] as $key => $val)
	{
		$str = $lng->txt($key);
		if ($key == "title")
		{
			$str = $lng->txt("person_title");
		}
		$tpl->setVariable("TXT_".strtoupper($key), $str);

		if ($key == "default_role")
		{
			$tpl->setVariable(strtoupper($key), $val);
		}
		else
		{
			$tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val,true));
		}
	}

	$tpl->setVariable("FORMACTION", "register.php?cmd=save&lang=".$_GET["lang"]);
	$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	$tpl->setVariable("TXT_REQUIRED_FIELDS", $lng->txt("required_field"));
	$tpl->setVariable("TXT_LOGIN_DATA", $lng->txt("login_data"));
	$tpl->setVariable("TXT_PERSONAL_DATA", $lng->txt("personal_data"));
	$tpl->setVariable("TXT_CONTACT_DATA", $lng->txt("contact_data"));
	$tpl->setVariable("TXT_SETTINGS", $lng->txt("settings"));
	$tpl->setVariable("TXT_PASSWD2", $lng->txt("retype_password"));
	$tpl->setVariable("TXT_LANGUAGE",$lng->txt("language"));
	$tpl->setVariable("TXT_GENDER_F",$lng->txt("gender_f"));
	$tpl->setVariable("TXT_GENDER_M",$lng->txt("gender_m"));

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
			$tpl->setVariable("LANG_NAME", $lng->txt("lang_".$lang_key));
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
	$selected_lang = (isset($_SESSION["error_post_vars"]["Fobject"]["language"])) ? $_SESSION["error_post_vars"]["Fobject"]["language"] : $lng->lang_key;

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
		foreach ($_SESSION["error_post_vars"]["Fobject"] as $key => $val)
		{
			if ($key != "default_role" and $key != "language")
			{
				$tpl->setVariable(strtoupper($key), ilUtil::prepareFormOutput($val,true));
			}
		}

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
	$tpl->setVariable("AGREEMENT", getUserAgreement());
	$tpl->setVariable("ACCEPT_CHECKBOX", ilUtil::formCheckbox(0, "status", "accepted"));
	$tpl->setVariable("ACCEPT_AGREEMENT",$lng->txt("accept_usr_agreement") );

	$tpl->show();

}

function getUserAgreement()
{
	global $lng, $ilias;

	$tmpPath = getcwd();
	$tmpsave = getcwd();
	$agrPath = $tmpPath."/agreement";
	chdir($agrPath);

	$agreement = "agreement_".$lng->lang_key.".html";

	// fallback to default language if selected translated user agreement of selected language was not found
	if (!file_exists($agreement))
	{
		$agreement = "agreement_".$lng->lang_default.".html";
	}
	
	if (file_exists($agreement))
	{
		if ($content = file($agreement))
		{
			foreach ($content as $key => $val)
			{
				$text .= trim(nl2br($val));
			}
			chdir($tmpsave);
			return $text;
		}
		else
		{
			$ilias->raiseError($lng->txt("usr_agreement_empty"),$ilias->error_obj->MESSAGE);
		}
	}
	else
	{
		$ilias->raiseError($lng->txt("file_not_found"),$ilias->error_obj->MESSAGE);
	}
	
	chdir($tmpsave);
}
?>

