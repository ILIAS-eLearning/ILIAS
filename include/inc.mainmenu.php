<?php
/**
* main menu
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*/
if ($rbacsystem->checkAccess("write", SYSTEM_FOLDER_ID, ROOT_FOLDER_ID))
{
	$tpl->setCurrentBlock("userisadmin");
	$tpl->setVariable("TXT_ADMINISTRATION", $lng->txt("administration"));
	$tpl->parseCurrentBlock();
}
// limit access only to authors
if ($rbacsystem->checkAccess("write", ROOT_FOLDER_ID, 0))
{
	$tpl->setCurrentBlock("userisauthor");
	$tpl->setVariable("TXT_EDITOR", $lng->txt("editor"));
	$tpl->parseCurrentBlock();
}

if(is_dir($tpl->tplPath."/".$ilias->account->prefs["style"]))
	$image_dir = "/".$ilias->account->prefs["style"];
else
	$image_dir = "";

$tpl->setCurrentBlock("navigation");
$tpl->setVariable("IMAGE_DIR", $image_dir);
$tpl->setVariable("TXT_PERSONAL_DESKTOP", $lng->txt("personal_desktop"));
$tpl->setVariable("TXT_LO_OVERVIEW", $lng->txt("lo_overview"));
$tpl->setVariable("TXT_BOOKMARKS", $lng->txt("bookmarks"));
$tpl->setVariable("TXT_SEARCH", $lng->txt("search"));
$tpl->setVariable("TXT_LITERATURE", $lng->txt("literature"));
$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));
$tpl->setVariable("TXT_FORUMS", $lng->txt("forums"));
$tpl->setVariable("TXT_GROUPS", $lng->txt("groups"));
$tpl->setVariable("TXT_HELP", $lng->txt("help"));
$tpl->setVariable("TXT_FEEDBACK", $lng->txt("feedback"));
$tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
$tpl->parseCurrentBlock();
?>
