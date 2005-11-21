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
* forums_threads_view
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilObjForum.php";
require_once "./classes/class.ilFileDataForum.php";

$lng->loadLanguageModule("forum");

$forumObj = new ilObjForum($_GET["ref_id"]);
$frm =& $forumObj->Forum;

$frm->setForumId($forumObj->getId());
$frm->setForumRefId($forumObj->getRefId());

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_notification.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
$tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
// catch stored message
sendInfo();
// display infopanel if something happened
infoPanel();

if (!$rbacsystem->checkAccess("read,visible", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

// get forum- and thread-data
$frm->setWhereCondition("top_frm_fk = ".$frm->getForumId());

if (is_array($topicData = $frm->getOneTopic()))
{
	$frm->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
	$threadData = $frm->getOneThread();

	$tpl->setCurrentBlock("header_image");
	$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_frm_b.gif"));
	$tpl->parseCurrentBlock();
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("forums_thread")." \"".$threadData["thr_subject"]."\"");

	// ********************************************************************************
	// build location-links
	include_once("classes/class.ilForumLocatorGUI.php");
	$frm_loc =& new ilForumLocatorGUI();
	$frm_loc->setRefId($_GET["ref_id"]);
	$frm_loc->setForum($frm);
	$frm_loc->setThread($_GET["thr_pk"], $threadData["thr_subject"]);
	$frm_loc->display();

	// set tabs
	// display different buttons depending on viewmode

	$session_name = "viewmode_".$forumObj->getId();
	if (!isset($_SESSION[$session_name]) or $_SESSION[$session_name] == "flat")
	{
		$ftabtype = "tabactive";
		$ttabtype = "tabinactive";
	}
	else
	{
		$ftabtype = "tabinactive";
		$ttabtype = "tabactive";
	}

	$tpl->setCurrentBlock("tab");	
	$tpl->setVariable("TAB_TYPE", "tabinactive");
	$tpl->setVariable("TAB_LINK", "forums_frameset.php?viewmode=tree&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
	$tpl->setVariable("TAB_TEXT", $lng->txt("order_by")." ".$lng->txt("answers"));
	$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
	$tpl->setVariable("TAB_TARGET", $t_frame);
	$tpl->parseCurrentBlock();

	$tpl->setCurrentBlock("tab");
	$tpl->setVariable("TAB_TYPE", "tabinactive");
	$tpl->setVariable("TAB_LINK", "forums_frameset.php?viewmode=flat&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
	$tpl->setVariable("TAB_TEXT", $lng->txt("order_by")." ".$lng->txt("date"));
	$tpl->setVariable("TAB_TARGET", $t_frame);
	$tpl->parseCurrentBlock();

	$tpl->setCurrentBlock("tab");
	$tpl->setVariable("TAB_TYPE", "tabactive");
	$tpl->setVariable("TAB_LINK", "forums_threads_notification.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
	$tpl->setVariable("TAB_TEXT", $lng->txt("forums_notification"));
	$tpl->setVariable("TAB_TARGET", $t_frame);
	$tpl->parseCurrentBlock();

	// ********************************************************************************

	// form processing (edit & reply)
	if ($_GET["cmd"] == "enable_notification")
	{
		$frm->enableNotification($ilUser->getId(), $_GET["thr_pk"]);
		sendInfo($lng->txt("forums_notification_enabled"));
	}
	else if ($_GET["cmd"] == "disable_notification")
	{
		$frm->disableNotification($ilUser->getId(), $_GET["thr_pk"]);
		sendInfo($lng->txt("forums_notification_disabled"));
	}

	if ($frm->isNotificationEnabled($ilUser->getId(), $_GET["thr_pk"]))
	{
		$tpl->setVariable("TXT_STATUS", $lng->txt("forums_notification_is_enabled"));
		$tpl->setVariable("TXT_SUBMIT", $lng->txt("forums_disable_notification"));
		$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=disable_notification&thr_pk=".$_GET["thr_pk"]."&ref_id=".$forumObj->getRefId());
	}
	else
	{
		$tpl->setVariable("TXT_STATUS", $lng->txt("forums_notification_is_disabled"));
		$tpl->setVariable("TXT_SUBMIT", $lng->txt("forums_enable_notification"));
		$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=enable_notification&thr_pk=".$_GET["thr_pk"]."&ref_id=".$forumObj->getRefId());
	}
}

$tpl->show();
?>
