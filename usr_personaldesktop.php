<?php
/**
 * personal desktop
 * welcome screen of ilias with new mails, last lo's etc.
 * adapted from ilias 2
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-pgm
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include_once("./include/inc.main.php");

$lng = new Language($ilias->account->data["language"]);

//$tplmain->setVariable("TXT_PAGETITLE","ILIAS - ".$lng->txt("personal_desktop"));

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_profile.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("personal_profile"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_password.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("chg_password"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","usr_agreement.php");
$tplbtn->setVariable("BTN_TXT",$lng->txt("usr_agreement"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl = new Template("tpl.usr_personaldesktop.html", true, true);
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("personal_desktop"));
$tpl->setVariable("BUTTONS",$tplbtn->get());

//mails
$mails = $ilias->account->getUnreadMail();
//last visited lessons
$lessonsLastVisited = $ilias->account->getLastVisitedLessons();
//courses
$courses = $ilias->account->getCourses();

//********************************************
//* OUTPUT
//********************************************

//begin mailblock if there are new mails
if (count($mails)>0)
{
	unset($i);
	foreach ($mails as $row)
	{
		$i++;
		$tpl->setCurrentBlock("tbl_mail_row");
		$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
		$tpl->setVariable("MAIL_SND_LNAME", $row["from"]);
		$tpl->setVariable("MAIL_SND_FNAME", "");
		$tpl->setVariable("MAIL_SND_NICK", "");
		$tpl->setVariable("MAIL_SND", "");
		$tpl->setVariable("MAIL_SUBJ", $row["subject"]);
		$tpl->setVariable("MAIL_DATE", $row["datetime"]);
		$tpl->setVariable("MAIL_LINK_READ", "mail.php?id=".$row["id"]);
		$tpl->setVariable("MAIL_LINK_DEL","");
		$tpl->setVariable("TXT_DELETE",$lng->txt("delete"));
		$tpl->setVariable("TXT_ARE_YOU_SURE",$lng->txt("are_you_sure"));
		$tpl->parseCurrentBlock();
	}
	$tpl->setCurrentBlock("tbl_mail");
	//headline
	$tpl->setVariable("MAIL_COUNT",count($mails));
	$tpl->setVariable("TXT_MAIL_S",$lng->txt("mail_s_unread"));
	//columns headlines
	$tpl->setVariable("TXT_SENDER", $lng->txt("sender"));
	$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
//	$tpl->setVariable("MAIL_SORT_SUBJ","link");
	$tpl->setVariable("TXT_DATETIME",$lng->txt("date")."/".$lng->txt("time"));
//	$tpl->setVariable("MAIL_SORT_DATE","link");
	$tpl->parseCurrentBlock();
}

//if there are lessons output them
if (count($lessonsLastVisited)>0)
{
	unset($i);
	foreach ($lessonsLastVisited as $row)
	{
		$i++;
		$tpl->setCurrentBlock("tbl_lo_row");
		$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));
		$tpl->setVAriable("LO_TIME", $row["datetime"]);
		$tpl->setVAriable("LO_LINK_LO", "lesson.php?id=".$row["id"]);
		$tpl->setVAriable("LO_LINK_LO_PAGE", "lesson.php?id=".$row["id"]."&amp;page=".$row["pageid"]);
		$tpl->setVAriable("LO_TITLE", $row["title"]);
		$tpl->setVAriable("LO_PAGE", $row["page"]);
		$tpl->parseCurrentBlock();
	}
	$tpl->setCurrentBlock("tbl_lo");
	$tpl->setVariable("TXT_LO_HEADER",$lng->txt("last_visited_lessons"));
	$tpl->setVariable("TXT_LO_TIME",$lng->txt("time"));
	$tpl->setVariable("TXT_LO_TITLE",$lng->txt("lesson"));
	$tpl->setVariable("TXT_LO_PAGE",$lng->txt("page"));
	$tpl->parseCurrentBlock();
}


//Courses
if (count($courses)>0)
{
	unset($i);
	foreach ($courses as $row)
	{
		$i++;
		$tpl->setCurrentBlock("tbl_crs_row");
		$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
		$tpl->setVariable("CRS_TITLE", $row["title"]);
		$tpl->setVariable("CRS_DESC", $row["desc"]);
		$tpl->setVariable("CRS_LINK", "course.php?id=".$row["id"]);
		$tpl->setVariable("TXT_QUIT", $lng->txt("quit"));
		$tpl->setVariable("CRS_LINK_QUIT", "course.php?id=".$row["id"]."&amp;func=quit");
		$tpl->parseCurrentBlock();
	}
	$tpl->setCurrentBlock("tbl_crs");
	$tpl->setVariable("TXT_COURSES", $lng->txt("courses"));
	$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
	$tpl->setVariable("TXT_DESC", $lng->txt("description"));
	$tpl->parseCurrentBlock();
}

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>