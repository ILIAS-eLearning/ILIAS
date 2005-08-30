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
require_once "classes/class.ilAddressbook.php";
require_once "classes/class.ilFormatMail.php";

$umail = new ilFormatMail($_SESSION["AccountId"]);

// catch hack attempts
if (!$rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
}

$lng->loadLanguageModule("mail");

$tpl->addBlockFile("CONTENT", "content", "tpl.mail_search.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->setVariable("TXT_SEARCH",$lng->txt("search"));
infoPanel();

// LOCATOR
setLocator($_GET["mobj_id"],"mail_search.php",$_SESSION["AccountId"],"");

// BUTTONS
include "./include/inc.mail_buttons.php";

$tpl->setVariable("ACTION","mail_new.php?mobj_id=$_GET[mobj_id]&type=search_res");

// BEGIN ADDRESSBOOK
if ($_GET["addressbook"])
{
	$tpl->setCurrentBlock("addr");
	$abook = new ilAddressbook($_SESSION["AccountId"]);
	$entries = $abook->searchUsers(addslashes(urldecode($_GET["search"])));

	if ($entries)
	{
		$counter = 0;
		$tpl->setCurrentBlock("addr_search");

		foreach ($entries as $entry)
		{
			$tpl->setVariable("ADDR_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
			$tpl->setVariable("ADDR_LOGIN_A",$entry["login"]);
			$tpl->setVariable("ADDR_LOGIN_B",$entry["login"]);
			$tpl->setVariable("ADDR_FIRSTNAME",$entry["firstname"]);
			$tpl->setVariable("ADDR_LASTNAME",$entry["lastname"]);
			$tpl->setVariable("ADDR_EMAIL_A",$entry["email"]);
			$tpl->setVariable("ADDR_EMAIL_B",$entry["email"]);
			$tpl->parseCurrentBlock();
		}		
	}
	else
	{
		$tpl->setCurrentBlock("addr_no_content");
		$tpl->setVariable("TXT_ADDR_NO",$lng->txt("mail_search_no"));
		$tpl->parseCurrentBlock();
	}
	
	// SET TXT VARIABLES ADDRESSBOOK
	$tpl->setVariable("TXT_ADDR",$lng->txt("mail_addressbook"));
	$tpl->setVariable("TXT_ADDR_PERSONS",$lng->txt("persons"));
	$tpl->setVariable("TXT_ADDR_LOGIN",$lng->txt("login"));
	$tpl->setVariable("TXT_ADDR_FIRSTNAME",$lng->txt("firstname"));
	$tpl->setVariable("TXT_ADDR_LASTNAME",$lng->txt("lastname"));
	$tpl->setVariable("TXT_ADDR_EMAIL",$lng->txt("email"));
	$tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

if ($_GET["system"])
{
	$user = new ilObjUser();
	$users = $user->searchUsers(addslashes(urldecode($_GET["search"])));

	if ($users)
	{
		$counter = 0;

		foreach ($users as $user_data)
		{
			if ($rbacsystem->checkAccess("smtp_mail",$umail->getMailObjectReferenceId()))
			{
				$tpl->setCurrentBlock("smtp_row");
				$tpl->setVariable("PERSON_EMAIL",$user_data["email"]);
				$tpl->setVariable("EMAIL",$user_data["email"]);
				$tpl->parseCurrentBlock();
			}
			$tpl->setCurrentBlock("person_search");
			$tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
			$tpl->setVariable("PERSON_LOGIN",$user_data["login"]);
			$tpl->setVariable("LOGIN",$user_data["login"]);
			$tpl->setVariable("FIRSTNAME",$user_data["firstname"]);
			$tpl->setVariable("LASTNAME",$user_data["lastname"]);
			$tpl->parseCurrentBlock();
		}
	}
	else
	{
		$tpl->setCurrentBlock("no_content");
		$tpl->setVariable("TXT_PERSON_NO",$lng->txt("mail_search_no"));
		$tpl->parseCurrentBlock();
	}

	$groups = ilUtil::searchGroups(addslashes(urldecode($_GET["search"])));

	if ($groups)
	{
		$counter = 0;
		$tpl->setCurrentBlock("group_search");

		foreach ($groups as $group_data)
		{
			$tpl->setVariable("GROUP_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
			$tpl->setVariable("GROUP_NAME","#".$group_data["title"]);
			$tpl->setVariable("GROUP_TITLE",$group_data["title"]);
			$tpl->setVariable("GROUP_DESC",$group_data["description"]);
			$tpl->parseCurrentBlock();
		}
	}
	else
	{
		$tpl->setCurrentBlock("no_content");
		$tpl->setVariable("TXT_GROUP_NO",$lng->txt("mail_search_no"));
		$tpl->parseCurrentBlock();
	}

	if ($rbacsystem->checkAccess("smtp_mail",$umail->getMailObjectReferenceId()))
	{
		$tpl->setCurrentBlock("smtp");
		$tpl->setVariable("TXT_EMAIL",$lng->txt("email"));
		$tpl->parseCurrentBlock();
	}
		
	$tpl->setCurrentBlock("system");
	$tpl->setVariable("TXT_PERSONS",$lng->txt("persons"));
	$tpl->setVariable("TXT_LOGIN",$lng->txt("login"));
	$tpl->setVariable("TXT_FIRSTNAME",$lng->txt("firstname"));
	$tpl->setVariable("TXT_LASTNAME",$lng->txt("lastname"));
	$tpl->setVariable("TXT_GROUPS",$lng->txt("groups"));
	$tpl->setVariable("TXT_GROUP_NAME",$lng->txt("title"));
	$tpl->setVariable("TXT_GROUP_DESC",$lng->txt("description"));
	$tpl->setVariable("BUTTON_ADOPT",$lng->txt("adopt"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
} 		

$tpl->show();
?>
