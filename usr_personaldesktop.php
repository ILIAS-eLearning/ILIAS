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
require_once "./include/inc.header.php";

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

//forums
$frm_obj = TUtil::getObjectsByOperations('frm','read');
$frmNum = count($frm_obj); 
$lastLogin = $ilias->account->data["LastLogin"];

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

//forums
if ($frmNum > 0)
{	
	// build list	
	require_once "classes/class.Forum.php";
	$frm = new Forum();
	$z = 0;
	
	foreach($frm_obj as $frm_data)
	{
		unset($topicData);
		
		// get forum data
		$frm->setWhereCondition("top_frm_fk = ".$frm_data["obj_id"]);			
		$topicData = $frm->getOneTopic();		
		
		$lastPost = "";
				
		if ($topicData["top_last_post"] != "") 
		{
			$lastPost = $frm->getLastPost($topicData["top_last_post"]);	
			
			$frm->setDbTable("frm_posts");			
			$frm->setWhereCondition("pos_pk = ".$lastPost["pos_pk"]);
			$posData = $frm->getOneDataset();	
			
			$stamp_post = mktime(substr($posData["pos_date"], 11, 2),substr($posData["pos_date"], 14, 2),substr($posData["pos_date"], 17, 2),substr($posData["pos_date"], 5, 2),substr($posData["pos_date"], 8, 2),substr($posData["pos_date"], 0, 4));
			$stamp_login = mktime(substr($lastLogin, 11, 2),substr($lastLogin, 14, 2),substr($lastLogin, 17, 2),substr($lastLogin, 5, 2),substr($lastLogin, 8, 2),substr($lastLogin, 0, 4));
						
			// if lastPost is more up to date than lastLogin ...
			if ($stamp_post > $stamp_login)
			{				
				if ($_GET["cmd"] == "list_forum")
				{
					$tpl->setCurrentBlock("tbl_frm_row");
					$rowCol = TUtil::switchColor($z,"tblrow2","tblrow1");
					$tpl->setVariable("ROWCOL", $rowCol);				
					$tpl->setVariable("FRM_TITLE","<a href=\"forums_threads_liste.php?obj_id=".$frm_data["obj_id"]."&parent=".$frm_data["parent"]."\">".$topicData["top_name"]."</a>");								
					$tpl->setVariable("LAST_POST", $lastPost["pos_date"]);
					$tpl->parseCurrentBlock("tbl_frm_row");
				}
				
				$z ++;
			}
				
		}		
		
	}
	
	// show table, when there are new entries
	if ($z > 0)
	{
		$tpl->setCurrentBlock("tbl_frm");
		$tpl->setVariable("TXT_FORUMS", $lng->txt("forums_new_entries"));
		
		if ($_GET["cmd"] == "list_forum") {
			$tpl->setVariable("TXT_TITLE", $lng->txt("forum"));
			$tpl->setVariable("TXT_LASTPOST", $lng->txt("forums_last_post"));
		}
		else {
			$tpl->setVariable("LIST_BUTTON", "<a href=\"usr_personaldesktop.php?cmd=list_forum\">".$lng->txt("show_list")."</a>");
		}
		$tpl->parseCurrentBlock("tbl_frm");
	}
	
}


$tpl->show();

?>