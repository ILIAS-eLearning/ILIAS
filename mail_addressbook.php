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
* mail search recipients,groups
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilMail.php";
require_once "classes/class.ilAddressbook.php";
require_once "classes/class.ilFormatMail.php";

$lng->loadLanguageModule("mail");

$umail = new ilFormatMail($_SESSION["AccountId"]);

// catch hack attempts
if (!$rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
}

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_addressbook.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->setCurrentBlock("header_image");
$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mail_b.gif"));
$tpl->parseCurrentBlock();
$tpl->setVariable("HEADER",$lng->txt("mail"));
infoPanel();

// LOCATOR
setLocator($_GET["mobj_id"],'mail_addressbook.php',$_SESSION["AccountId"],"");

// BUTTONS
include "./include/inc.mail_buttons.php";

$abook = new ilAddressbook($_SESSION["AccountId"]);

// ADD MAIL SENDER TO ADDRESSBOOK
if($_GET["type"] == 'add')
{
	$umail = new ilMail($_SESSION["AccountId"]);
	$mail_data = $umail->getMail($_GET["mail_id"]);

	$tmp_user = new ilObjUser($mail_data["sender_id"]);
	$abook->addEntry($tmp_user->getLogin(),
					 $tmp_user->getFirstname(),
					 $tmp_user->getLastname(),
					 $tmp_user->getEmail());
	sendInfo($lng->txt("mail_entry_added"));
}
// ACTIONS
if(isset($_POST["cmd"]["submit"]))
{
	switch($_POST["action"])
	{
		case 'edit':
			if(!is_array($_POST["entry_id"]))
			{
				unset($_POST["action"]);
				sendInfo($lng->txt("mail_select_one_entry"));
			}
			else
			{
				$tmp_abook = new ilAddressbook($_SESSION["AccountId"]);
				$data = $tmp_abook->getEntry($_POST["entry_id"][0]);
			}
			break;
		case 'delete':
				if(!is_array($_POST["entry_id"]))
				{
					sendInfo($lng->txt("mail_select_one_entry"));
					$error_delete = true;
				}
				else
				{
					sendInfo($lng->txt("mail_sure_delete_entry"));
				}
	}
}
// CHANGE ENTRY DATA
if(isset($_POST["cmd"]["change"]))
{
	if(!is_array($_POST["entry_id"]))
	{
		sendInfo($lng->txt("mail_select_one"));
	}
	// check if user login and e-mail-address are empty 
	else if (!strcmp(trim($_POST["login"]),"") &&
			 !strcmp(trim($_POST["email"]),""))
	{
		sendInfo($lng->txt("mail_enter_login_or_email_addr"));
		$error_add = true;
	}
	else if ($_POST["login"] != "" && 
			 !(ilObjUser::_lookupId($_POST["login"])))
	{
		sendInfo($lng->txt("mail_enter_valid_login"));
		$error_add = true;
	}
	else if ($_POST["email"] &&
			 !(ilUtil::is_email($_POST["email"])))
	{
		sendInfo($lng->txt("mail_enter_valid_email_addr"));
		$error_add = true;
	}
	else if (($existing_entry = $abook->checkEntry($_POST["login"])) > 0 &&
			 $existing_entry != $_POST["entry_id"][0])
	{
		sendInfo($lng->txt("mail_entry_exists"));
		$error_add = true;
	}
	else
	{
		$abook->updateEntry($_POST["entry_id"][0],
							$_POST["login"],
							$_POST["firstname"],
							$_POST["lastname"],
							$_POST["email"]);
		unset($_POST["entry_id"]);
		unset($existing_entry);
		sendInfo($lng->txt("mail_entry_changed"));
	}
}	

// CANCEL CONFIRM DELETE
if(isset($_POST["cmd"]["cancel"]))
{
	header("location:mail_addressbook.php?mobj_id=$_GET[mobj_id]&offset=$_GET[offset]");
	exit();
}

// ADD NEW ENTRY
if(isset($_POST["cmd"]["add"]))
{
	// check if user login and e-mail-address are empty 
	if (!strcmp(trim($_POST["login"]),"") &&
		!strcmp(trim($_POST["email"]),""))
	{
		sendInfo($lng->txt("mail_enter_login_or_email_addr"));
		$error_add = true;
	}
	else if ($_POST["login"] != "" && 
			 !(ilObjUser::_lookupId($_POST["login"])))
	{
		sendInfo($lng->txt("mail_enter_valid_login"));
		$error_add = true;
	}
	else if ($_POST["email"] &&
			 !(ilUtil::is_email($_POST["email"])))
	{
		sendInfo($lng->txt("mail_enter_valid_email_addr"));
		$error_add = true;
	}
	else if (($existing_entry = $abook->checkEntry($_POST["login"])) > 0)
	{
		sendInfo($lng->txt("mail_entry_exists"));
		$error_add = true;
	}
	else
	{
		$abook->addEntry($_POST["login"],
					 $_POST["firstname"],
					 $_POST["lastname"],
					 $_POST["email"]);
		sendInfo($lng->txt("mail_entry_added"));
	}
	
}

// CONFIRM DELETE
if(isset($_POST["cmd"]["confirm"]))
{
	if(!is_array($_POST["entry_id"]))
	{
		sendInfo($lng->txt("mail_select_one_entry"));
	}
	else if($abook->deleteEntries($_POST["entry_id"]))
	{
		$_GET["offset"] = 0;
		sendInfo($lng->txt("mail_deleted_entry"));
	}
	else
	{
		sendInfo($lng->txt("mail_delete_error"));
	}
}

$tpl->setVariable("ACTION","mail_addressbook.php?mobj_id=$_GET[mobj_id]&offset=$_GET[offset]");
$tpl->setVariable("TXT_ENTRIES",$lng->txt("mail_addr_entries"));

// CASE ENTRY EXISTS
if ((isset($_POST["cmd"]["add"]) || isset($_POST["cmd"]["change"])) &&
	$existing_entry > 0)
{
	$tpl->setCurrentBlock("entry_exists");
	$tpl->setVariable("ENTRY_EXISTS_ENTRY_ID",$existing_entry);
	$tpl->setVariable("ENTRY_EXISTS_BUTTON_OVERWRITE",$lng->txt("overwrite"));
	$tpl->setVariable("ENTRY_EXISTS_BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

// CASE CONFIRM DELETE
if($_POST["action"] == "delete" and !$error_delete and !isset($_POST["cmd"]["confirm"]))
{
	$tpl->setCurrentBlock("confirm_delete");
	$tpl->setVariable("BUTTON_CONFIRM",$lng->txt("confirm"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

// SET TXT VARIABLES ADDRESSBOOK
$tpl->setVariable("TXT_LOGIN",$lng->txt("username"));
$tpl->setVariable("TXT_FIRSTNAME",$lng->txt("firstname"));
$tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
$tpl->setVariable("TXT_EMAIL",$lng->txt("email"));
$tpl->setVariable("BUTTON_SUBMIT",$lng->txt("submit"));

// ACTIONS
$tpl->setCurrentBlock("actions");
$tpl->setVariable("ACTION_NAME","edit");
$tpl->setVariable("ACTION_VALUE",$lng->txt("edit"));
$tpl->parseCurrentBlock();

$tpl->setVariable("ACTION_NAME","delete");
$tpl->setVariable("ACTION_VALUE",$lng->txt("delete"));
$tpl->setVariable("ACTION_SELECTED",$_POST["cmd"] == 'delete' ? 'selected' : '');
$tpl->parseCurrentBlock();

$entries = $abook->getEntries();
$entries_count = count($entries);

// TODO: READ FROM MAIL_OPTIONS
$entries_max_hits = $ilUser->getPref('hits_per_page');
// SHOW ENTRIES
if($entries)
{
	$counter = 0;
	foreach($entries as $entry)
	{
		// LINKBAR
		if($entries_count > $entries_max_hits)
		{
			$params = array(
				"mobj_id"		=> $_GET["mobj_id"]);
		}
		$start = $_GET["offset"];
		$linkbar = ilUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$entries_count,$entries_max_hits,$start,$params);
		if ($linkbar)
		{
			$tpl->setVariable("LINKBAR", $linkbar);
		}
		if($counter >= ($start+$entries_max_hits))
		{
			break;
		}
		if($counter < $start)
		{
			++$counter;
			continue;
		}
		// END LINKBAR

		if($rbacsystem->checkAccess("smtp_mail",$umail->getMailObjectReferenceId()))
		{
			$tpl->setCurrentBlock("smtp");
			$tpl->setVariable("EMAIL_SMTP",$entry["email"]);
			$tpl->setVariable("EMAIL_LINK","./mail_new.php?mobj_id=".$_GET["mobj_id"].
									"&type=address&rcp=".urlencode($entry["email"]));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("no_smtp");
			$tpl->setVariable("EMAIL",$entry["email"]);
			$tpl->parseCurrentBlock();
		}
		$tpl->setCurrentBlock("addr_search");

		$tpl->setVariable("CSSROW", ilUtil::switchColor(++$couter,'tblrow1', 'tblrow2'));		
		if(is_array($_POST["entry_id"]))
		{
			$tpl->setVariable("CHECKED",in_array($entry["addr_id"],$_POST["entry_id"]) ? "checked='checked'" : "");
		}
		$tpl->setVariable("ENTRY_ID",$entry["addr_id"]);
		$tpl->setVariable("LOGIN_LINK","./mail_new.php?mobj_id=".$_GET["mobj_id"]."&type=address&rcp=".urlencode($entry["login"]));
		$tpl->setVariable("LOGIN",$entry["login"]);
		$tpl->setVariable("FIRSTNAME",$entry["firstname"]);
		$tpl->setVariable("LASTNAME",$entry["lastname"]);
		$tpl->parseCurrentBlock();
	}
	
	$tpl->setVariable("SELECT_ALL",$lng->txt('select_all'));	
	$tpl->setVariable("ROWCLASS", ilUtil::switchColor(++$couter,'tblrow1', 'tblrow2'));
}
else
{
	$tpl->setCurrentBlock("addr_no_content");
	$tpl->setVariable("TXT_ADDR_NO",$lng->txt("mail_search_no"));
	$tpl->parseCurrentBlock();
}

if (isset($_POST["cmd"]["add"]) &&
	$error_add)
{
	$data["login"] = $_POST["login"];
	$data["firstname"] = $_POST["firstname"];
	$data["lastname"] = $_POST["lastname"];
	$data["email"] = $_POST["email"];
}

// SHOW EDIT FIELD
$tpl->setVariable("CSSROW_LOGIN",'tblrow1');
$tpl->setVariable("HEADER_LOGIN",$lng->txt("username"));
$tpl->setVariable("VALUE_LOGIN",$data["login"]);
$tpl->setVariable("CSSROW_FIRSTNAME",'tblrow2');
$tpl->setVariable("HEADER_FIRSTNAME",$lng->txt("firstname"));
$tpl->setVariable("VALUE_FIRSTNAME",$data["firstname"]);
$tpl->setVariable("CSSROW_LASTNAME",'tblrow1');
$tpl->setVariable("HEADER_LASTNAME",$lng->txt("lastname"));
$tpl->setVariable("VALUE_LASTNAME",$data["lastname"]);
$tpl->setVariable("CSSROW_EMAIL",'tblrow2');
$tpl->setVariable("HEADER_EMAIL",$lng->txt("email"));
$tpl->setVariable("VALUE_EMAIL",$data["email"]);

// SUBMIT VALUE DEPENDS ON $_POST["cmd"]

$tpl->setVariable("BUTTON_EDIT_ADD",(($_POST["action"] == "edit") and $_POST["cmd"]["submit"]) ? $lng->txt("change") : $lng->txt("add"));
$tpl->setVariable("BUTTON_EDIT_ADD_NAME",(($_POST["action"] == "edit") and $_POST["cmd"]["submit"]) ? "cmd[change]" : "cmd[add]");

$tpl->show();
?>
