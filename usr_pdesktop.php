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
* @author Pia Behr <p.behr@fh-aachen.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilMail.php";

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.usr_pdesktop.html");

// catch feedback message
//sendInfo();
// display infopanel if something happened
//infoPanel();
/*
$tpl->setCurrentBlock("subtitle");
$tpl->setVariable("TXT_SUBTITLE",strtolower($lng->txt("of"))." ".$ilias->account->getFullname());
$tpl->parseCurrentBlock();


// SYSTEM MAILS
$umail = new ilMail($_SESSION["AccountId"]);
$smails = $umail->getMailsOfFolder(0);
*/

/*
//forums
$frm_obj = ilUtil::getObjectsByOperations('frm','read');
$frmNum = count($frm_obj);
$lastLogin = $ilias->account->getLastLogin();
*/

//********************************************
//* OUTPUT
//********************************************
/*

// TO DO: Change display of received e-mails

//begin mailblock if there are new mails
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
}
*/

// get skin = path to the specific template
$template_path = $ilias->tplPath.$ilias->account->getPref("skin");

//if the user visited lessons output the last visited lesson
if ($_GET["cmd"] == "lvis_le")
{
// add template to greet the user
$tpl->addBlockFile("USR_TEXT", "usr_text", "tpl.usr_text.html");

	//greetings
	$login_name = $ilias->account->login;
	$greeting = "";
	//check if morning, afternoon, evening or night
	if (date("H:i",time()) > "06:00:00" and date("H:i",time()) < "12:00:00")
		{$greeting .= $lng->txt("ingmedia_good_morning");}
	elseif (date("H:i",time()) > "12:00:00" and date("H:i",time()) < "18:00:00")
		{$greeting .= $lng->txt("ingmedia_good_afternoon");}
	elseif (date("H:i",time()) > "18:00:00" or date("H:i",time()) < "22:00:00")
		{$greeting .= $lng->txt("ingmedia_good_evening");}
	elseif (date("H:i",time()) < "06:00:00" or date("H:i",time()) > "22:00:00")
		{$greeting .= $lng->txt("ingmedia_good_night");}
	else
		{$greeting .= $lng->txt("ingmedia_hello");};
	$greeting .= " ".$login_name;
	//write these lines on the top of the frame "bottom"
	$greeting .= $lng->txt("ingmedia_welcome");

	// get all lm visited
	$result = $ilias->account->getLastVisitedLessons();
	if (sizeof($result) > 0)
	{
		$akt_date =  ilFormat::formatDate($result[0]["timestamp"],"date");
		$info ="";
		$info = $lng->txt("ingmedia_info_about_work1");
		$info .= " ".$akt_date." ";
		$info .= $lng->txt("ingmedia_info_about_work2");
		$tpl->setCurrentBlock("link_button");
		$tpl->setVariable("TXT_LINK", "content/lm_presentation.php?ref_id=".$result[0]["lm_id"]."&obj_id=".$result[0]["obj_id"]);
		$tpl->setVariable("TXT_LINK_IMG", $template_path."/images/layout/open.gif");
		$tpl->setVariable("TXT_LINK_TITLE", $result[0]["lm_title"]);
		$tpl->parseCurrentBlock();
	}
	else
	{
		// no last lo for this user found, so inform user and link back to desktop
		$info ="";
	}
	$tpl->setCurrentBlock("using_info");
	$tpl->setVariable("TXT_TITLE",$greeting);
	$tpl->setVariable("TXT_NOTES",$info);
	$tpl->parseCurrentBlock();
}

//if the user visited lessons output the last visited lesson
if ($_GET["cmd"] == "visited")
{
	// add template to greet the user
	$tpl->addBlockFile("USR_TEXT", "usr_text", "tpl.usr_text.html");

	// get all lm visited
	$result = $ilias->account->getLastVisitedLessons();
	if (sizeof($result) > 0)
	{
		for ($i=1;$i<=sizeof($result);$i++)
		{
			$tpl->setCurrentBlock("link_button");
			$tpl->setVariable("TXT_LINK", "content/lm_presentation.php?ref_id=".$result[$i-1]["lm_id"]."&obj_id=".$result[$i-1]["obj_id"]);
			$tpl->setVariable("TXT_LINK_TITLE", ilFormat::formatDate($result[$i-1]["timestamp"],"date")." - ".$result[$i-1]["lm_title"]);
			$tpl->setVariable("TXT_LINK_IMG", $template_path."/images/layout/open.gif");
			$tpl->parseCurrentBlock();
		}
	}
	// TO DO: use my own profile
	$tpl->setCurrentBlock("using_info");
	$tpl->setVariable("TXT_TITLE",$lng->txt("ingmedia_visited_le"));
	$tpl->parseCurrentBlock();
}

//Courses
if ($_GET["cmd"] == "courses")
{
	require_once "./classes/class.ilLOListGUI.php";
	$list_gui =& new ilLOListGUI();
}

if ($_GET["cmd"] == "whois")
{
        $users = ilUtil::getUsersOnline();

        $z = 0;

        foreach ($users as $user)
        {

                $rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
                $login_time = ilFormat::dateDiff(ilFormat::datetime2unixTS($user["last_login"]),time());

                $tpl->setCurrentBlock("tbl_users_row");
                $tpl->setVariable("ROWCOL",$rowCol);
                $tpl->setVariable("USR_LOGIN",$user["login"]);
                $tpl->setVariable("USR_TITLE",$user["title"]);
                $tpl->setVariable("USR_FIRSTNAME",$user["firstname"]);
                $tpl->setVariable("USR_LASTNAME",$user["lastname"]);
                $tpl->setVariable("USR_LOGIN_TIME",$login_time);
                $tpl->parseCurrentBlock();

                $z++;
        }

        $tpl->setCurrentBlock("tbl_users");
        $tpl->setVariable("TXT_USERS_ONLINE",$lng->txt("users_online"));
        $tpl->setVariable("TXT_USR_LOGIN",ucfirst($lng->txt("username")));
        $tpl->setVariable("TXT_USR_TITLE",ucfirst($lng->txt("title")));
        $tpl->setVariable("TXT_USR_FIRSTNAME",ucfirst($lng->txt("firstname")));
        $tpl->setVariable("TXT_USR_LASTNAME",ucfirst($lng->txt("lastname")));
        $tpl->setVariable("TXT_USR_LOGIN_TIME",ucfirst($lng->txt("login_time")));
        $tpl->parseCurrentBlock();
}
// output
$tpl->show();
?>