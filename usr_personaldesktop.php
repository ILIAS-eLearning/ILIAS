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
		$groupObj->leave($ilias->account->getId());
		$ilias->account->dropDesktopItem($groupObj->getRefId(), $groupObj->getType());
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

//********************************************
// learning modules & courses
$lo_items = $ilias->account->getDesktopItems("lm");
$i = 0;

foreach ($lo_items as $lo_item)
{
	$i++;
	$tpl->setCurrentBlock("tbl_lo_row");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("LO_LINK", "content/lm_presentation.php?ref_id=".$lo_item["id"].
		"&obj_id=".$lo_item["parameters"]);
	$tpl->setVariable("LO_TITLE", $lo_item["title"]);
	$tpl->setVariable("DROP_LINK", "usr_personaldesktop.php?cmd=dropItem&type=lm&id=".$lo_item["id"]);
	$tpl->setVariable("TXT_DROP", "(".$lng->txt("drop").")");
	$tpl->parseCurrentBlock();
}

if ($i == 0)
{
	$tpl->setCurrentBlock("tbl_no_lo");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("TXT_NO_LO", $lng->txt("no_lo_in_personal_list"));
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("tbl_lo");
$tpl->setVariable("TXT_LO_HEADER",$lng->txt("my_los"));
$tpl->setVariable("TXT_LO_TITLE",$lng->txt("title"));
$tpl->parseCurrentBlock();

//********************************************
// forums
$frm_items = $ilias->account->getDesktopItems("frm");
$i = 0;

foreach ($frm_items as $frm_item)
{
	$i++;
	$tpl->setCurrentBlock("tbl_frm_row");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("FRM_LINK", "forums_threads_liste.php?ref_id=".$frm_item["id"]."&backurl=forums");
	$tpl->setVariable("FRM_TITLE", $frm_item["title"]);
	$tpl->setVariable("DROP_LINK", "usr_personaldesktop.php?cmd=dropItem&type=frm&id=".$frm_item["id"]);
	$tpl->setVariable("TXT_DROP", "(".$lng->txt("drop").")");
	$tpl->parseCurrentBlock();
}

if ($i == 0)
{
	$tpl->setCurrentBlock("tbl_no_frm");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("TXT_NO_FRM", $lng->txt("no_frm_in_personal_list"));
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("tbl_frm");
$tpl->setVariable("TXT_FRM_HEADER",$lng->txt("my_frms"));
$tpl->setVariable("TXT_FRM_TITLE",$lng->txt("title"));
$tpl->parseCurrentBlock();

//********************************************
// users online
$tpl->setVariable("TXT_USERS_ONLINE",$lng->txt("users_online"));	

$users = ilUtil::getUsersOnline();

$num = 0;
	
foreach ($users as $user_id => $user)
{
	if ($user_id != ANONYMOUS_USER_ID)
	{
		$num++;
	}
	else
	{
		$guests = $user["num"];
	}
}

// parse guests text
if (empty($guests))
{
	$guest_text = "";
}
elseif ($guests == "1")
{
	$guest_text = "1 ".$lng->txt("guest");	
}
else
{
	$guest_text = $guests." ".$lng->txt("guests");		
}

// parse registered users text
if ($num > 0)
{
	if ($num == 1)
	{
		$user_list = $num." ".$lng->txt("registered_user");	
	}
	else
	{
		$user_list = $num." ".$lng->txt("registered_users");	
	}
	
	// add details link
	if ($_GET["cmd"] == "whoisdetail")
	{
		$text = $lng->txt("hide_details");
		$cmd = "hidedetails";
	}
	else
	{
		$text = $lng->txt("show_details");
		$cmd = "whoisdetail";
	}
		
	$user_details_link = "<a class=\"std\" href=\"usr_personaldesktop.php?cmd=".$cmd."\"> [".$text."]</a>";
		
	if (!empty($guest_text))
	{
		$user_list .= " ".$lng->txt("and")." ".$guest_text;
	}
		
	$user_list .= $user_details_link;
}
else
{
	$user_list = $guest_text;
}
	
$tpl->setVariable("USER_LIST",$user_list);		

// display details of users online
if ($_GET["cmd"] == "whoisdetail")
{
	$z = 0;
	
	foreach ($users as $user_id => $user)
	{
		if ($user_id != ANONYMOUS_USER_ID)
		{
			$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
			$login_time = ilFormat::dateDiff(ilFormat::datetime2unixTS($user["last_login"]),time());

			// hide mail-to icon for anonymous users
			if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID and $_SESSION["AccountId"] != $user_id)
			{
				$tpl->setCurrentBlock("mailto_link");
				$tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("icon_pencil_b.gif", false));
				$tpl->setVariable("ALT_TXT_MAIL",$lng->txt("mail"));
				$tpl->setVariable("USR_LOGIN",$user["login"]);	
				$tpl->parseCurrentBlock();
			}		

			// check for profile
			$q = "SELECT value FROM usr_pref WHERE usr_id='".$user_id."' AND keyword='public_profile' AND value='y'";
			$r = $ilias->db->query($q);

			if ($r->numRows())
			{
				$tpl->setCurrentBlock("profile_link");
				$tpl->setVariable("IMG_VIEW", ilUtil::getImagePath("enlarge.gif", false));
				$tpl->setVariable("ALT_TXT_VIEW",$lng->txt("view"));
				$tpl->setVariable("USR_ID",$user_id);
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("tbl_users_row");
			$tpl->setVariable("ROWCOL",$rowCol);		
			$tpl->setVariable("USR_LOGIN",$user["login"]);	
			$tpl->setVariable("USR_FULLNAME",ilObjUser::setFullname($user["title"],$user["firstname"],$user["lastname"]));
			$tpl->setVariable("USR_LOGIN_TIME",$login_time);
			
			$tpl->parseCurrentBlock();

			$z++;	
		}
	}
	
	if ($z > 0)
	{
		$tpl->setCurrentBlock("tbl_users_header");
		$tpl->setVariable("TXT_USR_LOGIN",ucfirst($lng->txt("username")));	
		$tpl->setVariable("TXT_USR_FULLNAME",ucfirst($lng->txt("fullname")));
		$tpl->setVariable("TXT_USR_LOGIN_TIME",ucfirst($lng->txt("login_time")));
		$tpl->parseCurrentBlock();
	}
}

//********************************************
// groups
$grp_items = $ilias->account->getDesktopItems("grp");
$i = 0;

foreach ($grp_items as $grp_item)
{
	$i++;
	$tpl->setCurrentBlock("tbl_grp_row");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("GRP_LINK", "group.php?ref_id=".$grp_item["id"]);
	$tpl->setVariable("GRP_TITLE", $grp_item["title"]);
	$tpl->setVariable("DROP_LINK", "usr_personaldesktop.php?cmd=leaveGroup&id=".$grp_item["id"]);
	$tpl->setVariable("TXT_DROP", "(".$lng->txt("unsubscribe").")");
	$tpl->parseCurrentBlock();
}

if ($i == 0)
{
	$tpl->setCurrentBlock("tbl_no_grp");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("TXT_NO_GRP", $lng->txt("no_grp_in_personal_list"));
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("tbl_grp");
$tpl->setVariable("TXT_GRP_HEADER",$lng->txt("my_grps"));
$tpl->setVariable("TXT_GRP_TITLE",$lng->txt("title"));
$tpl->parseCurrentBlock();

//********************************************
// bookmarks
$bm_items = $ilias->account->getDesktopItems("bm");
$i = 0;

foreach ($bm_items as $bm_item)
{
	$i++;
	$tpl->setCurrentBlock("tbl_bm_row");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("BM_LINK", "target URL");
	$tpl->setVariable("BM_TITLE", $bm_item["title"]);
	$tpl->setVariable("DROP_LINK", "usr_personaldesktop.php?cmd=dropItem&type=bm&id=".$bm_item["id"]);
	$tpl->setVariable("TXT_DROP", "(".$lng->txt("drop").")");
	$tpl->parseCurrentBlock();
}

if ($i == 0)
{
	$tpl->setCurrentBlock("tbl_no_bm");
	$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
	$tpl->setVariable("TXT_NO_BM", $lng->txt("no_bm_in_personal_list"));
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("tbl_bm");
$tpl->setVariable("TXT_BM_HEADER",$lng->txt("my_bms"));
$tpl->setVariable("TXT_BM_TITLE",$lng->txt("title"));
$tpl->parseCurrentBlock();

// output
$tpl->show();
?>
