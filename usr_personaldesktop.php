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
* personal desktop
* welcome screen of ilias with new mails, last lo's etc.
* adapted from ilias 2
*
* @author Peter Gabriel <pgabriel@databay.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilMail.php";
require_once "classes/class.ilPersonalDesktopGUI.php";

// catch hack attempts
if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
{
	$ilias->raiseError($lng->txt("msg_not_available_for_anon"),$ilias->error_obj->MESSAGE);
}

switch($_GET["cmd"])
{
	case "dropItem":
		$ilias->account->dropDesktopItem($_GET["id"], $_GET["type"]);
		break;

	case "leaveGroup":
		$groupObj = $ilias->obj_factory->getInstanceByRefId($_GET["id"]);
		$err_msg = $groupObj->removeMember($ilias->account->getId());
		if(strlen($err_msg) > 0)
			$ilias->raiseError($lng->txt($err_msg),$ilias->error_obj->MESSAGE);
		break;
}

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.usr_personaldesktop.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
//$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

// set locator
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("personal_desktop"));
$tpl->setVariable("LINK_ITEM", "usr_personaldesktop.php");
$tpl->parseCurrentBlock();

// catch feedback message
sendInfo();
// display infopanel if something happened
infoPanel();

// display tabs
include "./include/inc.personaldesktop_buttons.php";

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("personal_desktop"));
$tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));

/**
* TEMP. DISABLED
// SYSTEM MAILS
$umail = new ilMail($_SESSION["AccountId"]);
$smails = $umail->getMailsOfFolder(0);

// courses
$courses = $ilias->account->getCourses();

// forums
$frm_obj = ilUtil::getObjectsByOperations('frm','read');
$frmNum = count($frm_obj);
$lastLogin = $ilias->account->getLastLogin();
*/

//********************************************
//* OUTPUT
//********************************************

//begin mailblock if there are new mails
/*
if(count($smails))
{
	// output mails
	$counter = 1;
	foreach ($smails as $mail)
	{
		// GET INBOX FOLDER FOR LINK_READ
		require_once "classes/class.ilMailbox.php";

		$mbox = new ilMailbox($_SESSION["AccountId"]);
		$inbox = $mbox->getInboxFolder();

	    $tpl->setCurrentBlock("tbl_mail_row");
		$tpl->setVariable("ROWCOL",++$counter%2 ? 'tblrow1' : 'tblrow2');

		// GET SENDER NAME
		$user = new ilObjUser($mail["sender_id"]);

		//new mail or read mail?
		$tpl->setVariable("MAILCLASS", $mail["status"] == 'read' ? 'mailread' : 'mailunread');
		$tpl->setVariable("MAIL_FROM", $user->getFullname());
		$tpl->setVariable("MAIL_SUBJ", $mail["m_subject"]);
		$tpl->setVariable("MAIL_DATE", ilFormat::formatDate($mail["send_time"]));
		$target_name = htmlentities(urlencode("mail_read.php?mobj_id=".$inbox."&mail_id=".$mail["mail_id"]));
		$tpl->setVariable("MAIL_LINK_READ", "mail_frameset.php?target=".$target_name);
		$tpl->parseCurrentBlock();
	}
    $tpl->setCurrentBlock("tbl_mail");
   	//headline
	$tpl->setVariable("SYSTEM_MAILS",$lng->txt("mail_system"));
   	//columns headlines
    $tpl->setVariable("TXT_SENDER", $lng->txt("sender"));
   	$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
   	$tpl->setVariable("TXT_DATETIME",$lng->txt("date")."/".$lng->txt("time"));
   	$tpl->parseCurrentBlock();
}*/

$deskgui =& new ilPersonalDesktopGUI();

$deskgui->displayLearningResources();
$deskgui->displayForums();
$deskgui->displayUsersOnline();
$deskgui->displayGroups();
$deskgui->displayBookmarks();


// output
$tpl->show();
?>
