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
require_once "classes/class.ilUser.php";
require_once "classes/class.ilMail.php";
require_once "classes/class.ilAddressbook.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.mail_addressbook.html");
$tpl->setVariable("TXT_ADDRESSBOOK",$lng->txt("mail_addressbook"));
infoPanel();

// LOCATOR
setLocator($_GET["mobj_id"],$_SESSION["AccountId"],"");

// BUTTONS
include "./include/inc.mail_buttons.php";

$abook = new ilAddressbook($_SESSION["AccountId"]);

// ADD MAIL SENDER TO ADDRESSBOOK
if($_GET["type"] == 'add')
{
	$umail = new ilMail($_SESSION["AccountId"]);
	$mail_data = $umail->getMail($_GET["mail_id"]);

	$tmp_user = new ilUser($mail_data["sender_id"]);
	$abook->addEntry($tmp_user->getLogin(),
					 $tmp_user->getFirstname(),
					 $tmp_user->getLastname(),
					 $tmp_user->getEmail());
	sendInfo($lng->txt("mail_entry_added"));
}
// ACTIONS
if(isset($_POST["cmd"]))
{
	switch($_POST["cmd"])
	{

		case "cancel":
			header("location:mail_addressbook.php?mobj_id=$_GET[mobj_id]");
			exit();

		case 'edit':
			if(!is_array($_POST["entry_id"]))
			{
				sendInfo($lng->txt("mail_select_one_entry"));
			}
			else
			{
				$tmp_abook = new ilAddressbook($_SESSION["AccountId"]);
				$data = $tmp_abook->getEntry($_POST["entry_id"][0]);
			}
			break;
		case 'delete':
			if(isset($_POST["confirm"]))
			{
				if(!is_array($_POST["entry_id"]))
				{
					sendInfo($lng->txt("mail_select_one_entry"));
				}
				else if($abook->deleteEntries($_POST["entry_id"]))
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

		case $lng->txt("change"):
			if(!is_array($_POST["entry_id"]))
			{
				sendInfo($lng->txt("mail_select_one"));
			}
			else
			{
				$abook->updateEntry($_POST["entry_id"][0],
									$_POST["login"],
									$_POST["firstname"],
									$_POST["lastname"],
									$_POST["email"]);
				unset($_POST["entry_id"]);
				sendInfo($lng->txt("mail_entry_changed"));
			}
			break;

		case $lng->txt("add"):
			$abook->addEntry($_POST["login"],
							 $_POST["firstname"],
							 $_POST["lastname"],
							 $_POST["email"]);
			sendInfo($lng->txt("mail_entry_added"));
	}
}


$tpl->setVariable("ACTION","mail_addressbook.php?mobj_id=$_GET[mobj_id]");
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

// SHOW ENTRIES
if($entries)
{
	$counter = 0;
	$tpl->setCurrentBlock("addr_search");
	foreach($entries as $entry)
	{
		$tpl->setVariable("CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
		if(is_array($_POST["entry_id"]))
		{
			$tpl->setVariable("CHECKED",in_array($entry["addr_id"],$_POST["entry_id"]) ? 'checked' : '');
		}
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

// SHOW EDIT FIELD
$tpl->setVariable("CSSROW_LOGIN",'tblrow1');
$tpl->setVariable("HEADER_LOGIN",$lng->txt("login"));
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
$tpl->setVariable("BUTTON_EDIT_ADD",$_POST["cmd"] == 'edit' ? $lng->txt("change") : $lng->txt("add"));

$tpl->show();
?>
