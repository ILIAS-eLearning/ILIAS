<?php
/**
* change user password
* 
* @author	Peter Gabriel <pgabriel@databay.de> 
* @version	$Id$
* @package	ilias
*/
require_once "./include/inc.header.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.usr_password.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
// display infopanel if something happened
infoPanel();

//display buttons
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_profile.php");
$tpl->setVariable("BTN_TXT",$lng->txt("personal_profile"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_password.php");
$tpl->setVariable("BTN_TXT",$lng->txt("chg_password"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","usr_agreement.php");
$tpl->setVariable("BTN_TXT",$lng->txt("usr_agreement"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_row");
$tpl->parseCurrentBlock();

if ($_POST["save_passwd"])
{
	// check old password
	if (md5($_POST["pw_old"]) != $ilias->account->getPasswd())
	{
		$ilias->raiseError($lng->txt("passwd_wrong"),$ilias->error_obj->MESSAGE);
	}
	
	// check new password
	if ($_POST["pw1"] != $_POST["pw2"])
	{
		sendInfo($lng->txt("passwd_not_match"));
	}
			
	// validate password
	if (!ilUtil::is_password($_POST["pw1"]))
	{
		$ilias->raiseError($lng->txt("passwd_invalid"),$ilias->error_obj->MESSAGE);
	}
	
	if ($_POST["pw_old"] != "")
	{
		if ($ilias->account->updatePassword($_POST["pw_old"], $_POST["pw1"], $_POST["pw2"]))
		{
			sendInfo($lng->txt("msg_changes_ok"));
		}
		else
		{
			sendInfo($lng->txt("msg_failed"));
		}
	}
}

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("chg_password"));
$tpl->setVariable("TXT_CURRENT_PW", $lng->txt("current_password"));
$tpl->setVariable("TXT_DESIRED_PW", $lng->txt("desired_password"));
$tpl->setVariable("TXT_RETYPE_PW", $lng->txt("retype_password"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

$tpl->show();
?>
