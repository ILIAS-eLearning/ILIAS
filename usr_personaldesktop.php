<?php
/**
* personal desktop
* welcome screen of ilias with new mails, last lo's etc.
* adapted from ilias 2
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";

//add template for content
$tpl->addBlockFile("CONTENT", "content", "tpl.usr_personaldesktop.html");

//add template for buttons
$tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

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

$tpl->touchBlock("btn_row");

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("personal_desktop"));
$tpl->parseCurrentBlock();

//mails
$myMails = new UserMail($ilias->account->Id);
$mails = $myMails->getMail();

//last visited lessons
$lessonsLastVisited = $ilias->account->getLastVisitedLessons();

//courses
$courses = $ilias->account->getCourses();

//********************************************
//* OUTPUT
//********************************************

//begin mailblock if there are new mails
if ($mails["unread"]>0)
{
	// output mails
    unset($i);
	foreach ($mails["msg"] as $row)
	{
		$i++;
	    $tpl->setCurrentBlock("tbl_mail_row");
		$tpl->setVariable("ROWCOL","tblrow".(($i % 2)+1));

		//new mail or read mail?
		if ($row["new"] == true)
			$mailclass = "mailunread";
		else
			$mailclass = "mailread";
		
		$tpl->setVariable("MAILCLASS", $mailclass);
		$tpl->setVariable("MAIL_ID", $row["id"]);
		$tpl->setVariable("MAIL_FROM", $row["from"]);
		$tpl->setVariable("MAIL_SUBJ", $row["subject"]);
		$tpl->setVariable("MAIL_DATE", $row["datetime"]);
		$tpl->setVariable("MAIL_LINK_READ", "mail_read.php?id=".$row["id"]);
		$tpl->setVariable("MAIL_LINK_DEL", "");
		$tpl->setVariable("TXT_DELETE", $lng->txt("delete"));
		$tpl->setVariable("TXT_ARE_YOU_SURE", $lng->txt("are_you_sure"));
		$tpl->parseCurrentBlock();
	}
    $tpl->setCurrentBlock("tbl_mail");
   	//headline
	$tpl->setVariable("MAIL_COUNT", $mails["count"]);
    $tpl->setVariable("TXT_MAIL_S",$lng->txt("mail_s_unread"));
   	//columns headlines
    $tpl->setVariable("TXT_SENDER", $lng->txt("sender"));
   	$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
   	$tpl->setVariable("TXT_DATETIME",$lng->txt("date")."/".$lng->txt("time"));
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
                $tpl->setVAriable("LO_LINK_LO", "lo.php?id=".$row["id"]);
                $tpl->setVAriable("LO_LINK_LO_PAGE", "lo.php?id=".$row["id"]."&amp;page=".$row["pageid"]);
                $tpl->setVAriable("LO_TITLE", $row["title"]);
                $tpl->setVAriable("LO_PAGE", $row["page"]);
                $tpl->parseCurrentBlock();
        }
        $tpl->setCurrentBlock("tbl_lo");
        $tpl->setVariable("TXT_LO_HEADER",$lng->txt("los_last_visited"));
        $tpl->setVariable("TXT_LO_TIME",$lng->txt("time"));
        $tpl->setVariable("TXT_LO_TITLE",$lng->txt("lo"));
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
                $tpl->setVariable("CRS_LINK_QUIT", "course.php?id=".$row["id"]."&amp;cmd=quit");
                $tpl->parseCurrentBlock();
        }
		
        $tpl->setCurrentBlock("tbl_crs");
        $tpl->setVariable("TXT_COURSES", $lng->txt("courses"));
        $tpl->setVariable("TXT_TITLE", $lng->txt("title"));
        $tpl->setVariable("TXT_DESC", $lng->txt("description"));
        $tpl->parseCurrentBlock();
}

$tpl->show();

?>