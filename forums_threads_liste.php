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
* forums_threads_liste
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilObjForum.php";

$lng->loadLanguageModule("forum");

$forumObj = new ilObjForum($_GET["ref_id"]);
$frm = new ilForum();
$frm->setForumId($forumObj->getId());
$frm->setForumRefId($forumObj->getRefId());

$tpl->setVariable("TXT_PAGEHEADLINE", $forumObj->getTitle());
$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_liste.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
// display infopanel if something happened
infoPanel();

if (!$rbacsystem->checkAccess("read", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

// start: form operations
if (isset($_POST["cmd"]["submit"]))
{	
	if(is_array($_POST["forum_id"]))
	{
		$startTbl = "frm_threads";
		
		require_once "forums_export.php";		
		
		unset($topicData);
		
	}
	
}
// end: form operations

// ********************************************************************************
// build location-links
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("forums_overview"));
$tpl->setVariable("LINK_ITEM", "forums.php?ref_id=".$_GET["ref_id"]);
$tpl->parseCurrentBlock();

$frm->setWhereCondition("top_frm_fk = ".$frm->getForumId());

if (is_array($topicData = $frm->getOneTopic()))
{
	
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_topics_overview").": ".$topicData["top_name"]);
	$tpl->setVariable("LINK_ITEM", "forums_threads_liste.php?ref_id=".$_GET["ref_id"]);
	$tpl->parseCurrentBlock();

	if ($rbacsystem->checkAccess("write", $_GET["ref_id"]))
	{
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK","forums_threads_new.php?ref_id=".$_GET["ref_id"]);
		$tpl->setVariable("BTN_TXT", $lng->txt("forums_new_thread"));
		$tpl->parseCurrentBlock();
	}
	else
	{
		$tpl->setVariable("NO_BTN", "<br/><br/>");
	}

	// ********************************************************************************

	// Visit-Counter
	$frm->setDbTable("frm_data");
	$frm->setWhereCondition("top_pk = ".$topicData["top_pk"]);
	$frm->updateVisits($topicData["top_pk"]);
	
	// get list of threads
	$frm->setOrderField("thr_date DESC");
	$resThreads = $frm->getThreadList($topicData["top_pk"]);
	$thrNum = $resThreads->numRows();
	$pageHits = $frm->getPageHits();
	
	if ($thrNum > 0)
	{
		$z = 0;
		
		// navigation to browse
		if ($thrNum > $pageHits)
		{
			$params = array(
				"ref_id"		=> $_GET["ref_id"]	
			);
			
			if (!$_GET["offset"])
			{
				$Start = 0;
			}
			else
			{
				$Start = $_GET["offset"];
			}
			
			$linkbar = ilUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$thrNum,$pageHits,$Start,$params);
			
			if ($linkbar != "")
			{
				$tpl->setVariable("LINKBAR", $linkbar);
			}
		}
		
		// get threads dates
		while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($thrNum > $pageHits && $z >= ($Start+$pageHits))
			{
				break;
			}
		
			if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
			{
				$tpl->setCurrentBlock("threads_row");
				$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
				$tpl->setVariable("ROWCOL", $rowCol);
				
				$thrData["thr_date"] = $frm->convertDate($thrData["thr_date"]);
				$tpl->setVariable("DATE",$thrData["thr_date"]);
				$tpl->setVariable("TITLE","<a href=\"forums_frameset.php?thr_pk=".$thrData["thr_pk"]."&ref_id=".$_GET["ref_id"]."\">".$thrData["thr_subject"]."</a>");
				
				$tpl->setVariable("NUM_POSTS",$thrData["thr_num_posts"]);	
				
				$tpl->setVariable("NUM_VISITS",$thrData["visits"]);	
				
				// get author data
				unset($author);
				$author = $frm->getUser($thrData["thr_usr_id"]);	
				$tpl->setVariable("AUTHOR","<a href=\"forums_user_view.php?ref_id=".$_GET["ref_id"]."&user=".$thrData["thr_usr_id"]."&backurl=forums_threads_liste&offset=".$Start."\">".$author->getLogin()."</a>"); 
								
				// get last-post data
				$lpCont = "";				
				if ($thrData["thr_last_post"] != "")
				{
					$lastPost = $frm->getLastPost($thrData["thr_last_post"]);
				}

				if (is_array($lastPost))
				{				
					$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
					$lpCont = $lastPost["pos_date"]."<br/>".strtolower($lng->txt("from"))."&nbsp;";			
					$lpCont .= "<a href=\"forums_frameset.php?pos_pk=".$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&ref_id=".$_GET["ref_id"]."#".$lastPost["pos_pk"]."\">".$lastPost["login"]."</a>";
				}

				$tpl->setVariable("LAST_POST", $lpCont);	
				
				$tpl->setVariable("FORUM_ID", $thrData["thr_pk"]);		
				$tpl->setVariable("THR_TOP_FK", $thrData["thr_top_fk"]);		
				
				$tpl->setVariable("TXT_PRINT", $lng->txt("print"));
				
				$tpl->setVariable("THR_IMGPATH",$tpl->tplPath);
				
				$tpl->parseCurrentBlock("threads_row");
				
			} // if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
			
			$z ++;
			
		} // while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		
		$tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));		
		$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?ref_id=".$_GET["ref_id"]);
		$tpl->setVariable("TXT_OK",$lng->txt("ok"));			
		$tpl->setVariable("TXT_EXPORT_HTML", $lng->txt("export_html"));
		$tpl->setVariable("TXT_EXPORT_XML", $lng->txt("export_xml"));
		$tpl->setVariable("IMGPATH",$tpl->tplPath);
		
	} // if ($thrNum > 0)	
		
} // if (is_array($topicData = $frm->getOneTopic()))
else
{
	$tpl->setCurrentBlock("threads_no");
	$tpl->setVAriable("TXT_MSG_NO_THREADS_AVAILABLE",$lng->txt("forums_threads_not_available"));
	$tpl->parseCurrentBlock("threads_no");
}

$tpl->setCurrentBlock("threadtable");
$tpl->setVariable("COUNT_THREAD", $lng->txt("forums_count_thr").": ".$thrNum);
$tpl->setVariable("TXT_DATE", $lng->txt("date"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_TOPIC", $lng->txt("forums_thread"));
$tpl->setVariable("TXT_AUTHOR", $lng->txt("forums_thread_create_from"));
$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_articles"));
$tpl->setVariable("TXT_NUM_VISITS", $lng->txt("visits"));
$tpl->setVariable("TXT_LAST_POST", $lng->txt("forums_last_post"));
$tpl->parseCurrentBlock("threadtable");


// TODO: maybe obsolete
if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
	$tpl->setCurrentBlock("message2");
	$tpl->setVariable("MSG", urldecode( $_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TPLPATH", $tpl->vars["TPLPATH"]);

$tpl->show();
?>
