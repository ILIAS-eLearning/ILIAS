<?php
/**
* forums_threads_liste
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.Forum.php";

$frm = new Forum();

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_liste.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

if (!$rbacsystem->checkAccess("read", $_GET["obj_id"], $_GET["parent"])) {
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("forums_overview"));
$tpl->setVariable("LINK_ITEM", "forums.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
$tpl->parseCurrentBlock();

$frm->setWhereCondition("top_frm_fk = ".$_GET["obj_id"]);
if (is_array($topicData = $frm->getOneTopic())) {
	
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_topics_overview").": ".$topicData["top_name"]);
	$tpl->setVariable("LINK_ITEM", "forums_threads_liste.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
	$tpl->parseCurrentBlock();
	
	if ($rbacsystem->checkAccess("write", $_GET["obj_id"], $_GET["parent"]))
	{
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK","forums_threads_new.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
		$tpl->setVariable("BTN_TXT", $lng->txt("forums_new_thread"));
		$tpl->parseCurrentBlock();
	}
	else $tpl->setVariable("NO_BTN", "<br><br>"); 
	
	// Visit-Counter
	$frm->setDbTable("frm_data");
	$frm->setWhereCondition("top_pk = ".$topicData["top_pk"]);
	$frm->updateVisits($topicData["top_pk"]);
	
	$frm->setOrderField("thr_date DESC");
	$resThreads = $frm->getThreadList($topicData["top_pk"]);
	$thrNum = $resThreads->numRows();
	$pageHits = $frm->getPageHits();
	
	if ($thrNum > 0)
	{
		$z = 0;
		
		// Navigation zum Blättern der Seiten
		if ($thrNum > $pageHits)
		{
			$params = array(
				"obj_id"		=> $_GET["obj_id"],	
				"parent"		=> $_GET["parent"]		
			);
			
			if (!$_GET["offset"]) $Start = 0;
			else $Start = $_GET["offset"];
			
			$linkbar = TUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$thrNum,$pageHits,$Start,$params);
			
			if ($linkbar != "")
				$tpl->setVariable("LINKBAR", $linkbar);
		}
		
		while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		{
			
			if ($thrNum > $pageHits && $z >= ($Start+$pageHits))
			break;
		
			if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
			{
			
				$tpl->setCurrentBlock("threads_row");
				$rowCol = TUtil::switchColor($z,"tblrow2","tblrow1");
				$tpl->setVariable("ROWCOL", $rowCol);
				
				$thrData["thr_date"] = $frm->convertDate($thrData["thr_date"]);
				$tpl->setVariable("DATE",$thrData["thr_date"]);
				$tpl->setVariable("TITLE","<a href=\"forums_threads_view.php?thr_pk=".$thrData["thr_pk"]."&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."\">".$thrData["thr_subject"]."</a>");
				
				unset($author);
				$author = $frm->getModerator($thrData["thr_usr_id"]);	
				$tpl->setVariable("AUTHOR","<a href=\"forums_user_view.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&user=".$thrData["thr_usr_id"]."&backurl=forums_threads_liste&offset=".$Start."\">".$author["SurName"]."</a>"); 
				
				$tpl->setVariable("NUM_POSTS",$thrData["thr_num_posts"]);	
				
				$tpl->setVariable("NUM_VISITS",$thrData["visits"]);	
				
				$lpCont = "";				
				if ($thrData["thr_last_post"] != "") $lastPost = $frm->getLastPost($thrData["thr_last_post"]);	
				if (is_array($lastPost)) {				
					$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
					$lpCont = "<a href=\"forums_threads_view.php?pos_pk=".$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."#".$lastPost["pos_pk"]."\">".$lastPost["pos_message"]."</a><br>".$lng->txt("from")."&nbsp;";			
					$lpCont .= "<a href=\"forums_user_view.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&user=".$lastPost["pos_usr_id"]."&backurl=forums_threads_liste&offset=".$Start."\">".$lastPost["surname"]."</a><br>";
					$lpCont .= $lastPost["pos_date"];				
				}
				$tpl->setVariable("LAST_POST", $lpCont);			
				
				$tpl->parseCurrentBlock("threads_row");
			
			}
			
			$z ++;
		}
	}			
}
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
$tpl->setVariable("TXT_AUTHOR", $lng->txt("forums_thread_create"));
$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_articles"));
$tpl->setVariable("TXT_NUM_VISITS", $lng->txt("visits"));
$tpl->setVariable("TXT_LAST_POST", $lng->txt("forums_last_post"));
$tpl->parseCurrentBlock("threadtable");


if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
	$tpl->setCurrentBlock("message2");
	$tpl->setVariable("MSG", urldecode( $_GET["message"]));
	$tpl->parseCurrentBlock();
}


$tpl->show();

?>