<?php
/**
* editor view
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias-core
*/
require_once "./include/inc.header.php";

// limit access only to authors
if (!$rbacsystem->checkAccess("write", ROOT_FOLDER_ID, 0))
{
	$ilias->raiseError("You are not entitled to access this page!",$ilias->error_obj->WARNING);
}

$ilias->error_obj->sendInfo("Not available in this release.",$ilias->error_obj->MESSAGE);

$tpl->addBlockFile("CONTENT", "content", "tpl.editor.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

/*
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","???.php");
$tpl->setVariable("BTN_TXT", $lng->txt("test_intern"));

$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","lo_new.php");
$tpl->setVariable("BTN_TXT", $lng->txt("lo_new"));

$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","crs_edit.php");
$tpl->setVariable("BTN_TXT", $lng->txt("courses"));

$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_row");
$tpl->parseCurrentBlock();


for ($i = 0; $i < 5; $i++)
{
	$id = $i;
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL", "tblrow".(($i%2)+1));
	$tpl->setVAriable("DATE", date("d.m.Y H:i:s"));
	$tpl->setVAriable("TITLE", $lng->txt("lo").$i);
	$status = "on";
	$switchstatus = "off";
	$tpl->setVariable("LINK_STATUS", "editor.php?cmd=set".$switchstatus."line&amp;id=".$id);
	$tpl->setVariable("LINK_GENERATE", "editor.php?cmd=generate&amp;id=".$id);
	$tpl->setVariable("LINK_ANNOUNCE", "mail.php?cmd=announce&amp;id=".$id);
	$tpl->setVariable("LINK_EDIT", "lo_edit.php?id=".$id);
	$tpl->setVAriable("STATUS", $status);
	$tpl->setVariable("TXT_LO_SET_STATUS", $lng->txt("set_".$switchstatus."line"));
	$tpl->setVariable("TXT_ANNOUNCE", $lng->txt("announce"));
	$tpl->setVariable("TXT_GENERATE", $lng->txt("generate"));
	$tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
	$tpl->setVAriable("TITLE", $lng->txt("lo").$i);
	
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_LO_EDIT", $lng->txt("lo_edit"));

$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_ONLINE_VERSION", $lng->txt("online_version"));
$tpl->setVariable("TXT_OFFLINE_VERSION", $lng->txt("offline_version"));
$tpl->setVariable("TXT_PUBLISHED", $lng->txt("published"));
$tpl->parseCurrentBlock();
*/
$tpl->show();
?>