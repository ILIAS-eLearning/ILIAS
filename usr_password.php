<?php
/**
 * change user password
 * 
 * @author Peter Gabriel <pgabriel@databay.de> 
 * @version $Id$
 * @package ilias
 */
require_once "./include/ilias_header.inc";


$tplmain->setVariable("TXT_PAGETITLE", "ILIAS - " . $lng->txt("chg_password"));

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "usr_profile.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("personal_profile"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "usr_password.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("chg_password"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "usr_agreement.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("usr_agreement"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl = new Template("tpl.usr_password.html", true, true);

if ($_POST["pw_old"] != "")
{
	if ($ilias->account->updatePassword($_POST["pw_old"], $_POST["pw1"], $_POST["pw2"]) == true) {
		$msg = "msg_changes_ok";
	}
	else
		$msg = "msg_failed";
	
	$tpl->setCurrentBlock("message");
	$tpl->setVariable("MSG", $lng->txt($msg));
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("BUTTONS", $tplbtn->get());

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("chg_password"));

$tpl->setVariable("TXT_NAME", $lng->txt("username"));
$tpl->setVariable("NAME", $ilias->account->data["login"]);
$tpl->setVariable("TXT_CURRENT_PW", $lng->txt("current_password"));
$tpl->setVariable("TXT_DESIRED_PW", $lng->txt("desired_password"));
$tpl->setVariable("TXT_RETYPE_PW", $lng->txt("retype_password"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));

$tplmain->setVariable("PAGECONTENT", $tpl->get());
$tplmain->show();
?>