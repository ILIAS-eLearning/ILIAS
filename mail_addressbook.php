<?php
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
require_once "classes/class.User.php";
require_once "classes/class.Group.php";
require_once "classes/class.Addressbook.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.mail_addressbook.html");

// LOCATOR
setLocator($_GET["mobj_id"],$_SESSION["AccountId"],$lng->txt("mail_mails_of"));

// BUTTONS
include "./include/inc.mail_buttons.php";

$abook = new Addressbook($_SESSION["AccountId"]);

// ACTIONS
if(isset($_POST["cmd"]))
{
	switch($_POST["cmd"])
	{

		case "cancel":
			header("location:mail_addressbook.php?mobj_id=$_GET[mobj_id]");
			exit();

		case 'delete':
			if(isset($_POST["confirm"]))
			{
				if(!is_array($_POST["entry_id"]))
				{
					sendInfo($lng->txt("mail_select_one_entry"));
				}
				else if($abbok->deleteEntries($_POST["mail_id"]))
				{
					sendInfo($lng->txt("mail_deleted_entry"));
				}
				else
				{
					sendInfo($lng->txt("mail_delete_error"));
				}
				break;
			}
			else if(!isset($_POST["cancel"]))
			{ 
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
			else if(isset($_POST["cancel"]))
			{
				header("location: mail_addressbook.php?mobj_id=$_GET[mobj_id]");
				exit;
			}
			break;
	}
}


$tpl->setVariable("ACTION","mail_addressbook.php?mobj_id=$_GET[mobj_id]");
$tpl->setVariable("TXT_ADDRESSBOOK",$lng->txt("mail_addressbook"));
$tpl->setVariable("TXT_ENTRIES",$lng->txt("mail_addr_entries"));

// CASE CONFIRM DELETE
if($_POST["cmd"] == "delete" and !$error_delete and !isset($_POST["confirm"]))
{
	$tpl->setCurrentBlock("confirm_delete");
	$tpl->setVariable("BUTTON_CONFIRM",$lng->txt("confirm"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

// SET TXT VARIABLES ADDRESSBOOK
$tpl->setVariable("TXT_LOGIN",$lng->txt("login"));
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

if($entries)
{
	$counter = 0;
	$tpl->setCurrentBlock("addr_search");
	foreach($entries as $entry)
	{
		$tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
		$tpl->setVariable("ENTRY_ID",$entry["addr_id"]);
		$tpl->setVariable("LOGIN",$entry["login"]);
		$tpl->setVariable("FIRSTNAME",$entry["firstname"]);
		$tpl->setVariable("LASTNAME",$entry["lastname"]);
		$tpl->setVariable("EMAIL",$entry["email"]);
		$tpl->parseCurrentBlock();
	}		
}
else
{
	$tpl->setCurrentBlock("addr_no_content");
	$tpl->setVariable("TXT_ADDR_NO",$lng->txt("mail_search_no"));
	$tpl->parseCurrentBlock();
}

$tpl->show();
?>