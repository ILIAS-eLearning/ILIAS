<?php
/**
* forums_threads_view
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.Forum.php";

$frm = new Forum();

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_view.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

if (!$rbacsystem->checkAccess("read", $_GET["obj_id"], $_GET["parent"])) {
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->setVariable("TXT_FORUM_ARTICLES", $lng->txt("forums_posts"));

if ($_GET["feedback"] != "")
	$tpl->setVariable("TXT_FORM_FEEDBACK", $_GET["feedback"]);

// Sortier-Variablen für Posts
if ($_GET["orderby"] == "") $old_order = "answers";
else $old_order = $_GET["orderby"];
if ($old_order == "date") {
	$new_order = "answers";
	$orderField = "frm_posts_tree.date";
}
else {
	$new_order = "date";
	$orderField = "frm_posts_tree.rgt";
}
$tpl->setVariable("LINK_SORT", "<b>></b><a href=\"forums_threads_view.php?orderby=".$new_order."&thr_pk=".$_GET["thr_pk"]."&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."\">".$lng->txt("order_by")." ".$lng->txt($new_order)."</a>");

$frm->setWhereCondition("top_frm_fk = ".$_GET["obj_id"]);
if (is_array($topicData = $frm->getOneTopic())) {
	
	$frm->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
	$threadData = $frm->getOneThread();
	
	// Visit-Counter
	$frm->setDbTable("frm_threads");
	$frm->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
	$frm->updateVisits($_GET["thr_pk"]);
	
	// Link-Pfad 1.Teil
	$tpl->touchBlock("locator_separator");
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_overview"));
	$tpl->setVariable("LINK_ITEM", "forums.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
	$tpl->parseCurrentBlock();
	
	// Link-Pfad 2.Teil
	$tpl->touchBlock("locator_separator");
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_topics_overview").": ".$topicData["top_name"]);
	$tpl->setVariable("LINK_ITEM", "forums_threads_liste.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
	$tpl->parseCurrentBlock();	
	
	// Link-Pfad 3.Teil
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_thread_articles").": ".$threadData["thr_subject"]);
	$tpl->setVariable("LINK_ITEM", "forums_threads_view.php?thr_pk=".$_GET["thr_pk"]."&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
	$tpl->parseCurrentBlock();
	
	// Neuen Thread anlegen
	if ($rbacsystem->checkAccess("write", $_GET["obj_id"], $_GET["parent"]))
	{
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK","forums_threads_new.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
		$tpl->setVariable("BTN_TXT", $lng->txt("forums_new_thread"));
		$tpl->parseCurrentBlock();
	}
	else $tpl->setVariable("NO_BTN", "<br><br>"); 
	
	if ($_GET["cmd"] == "replypost")
	{		
		$formData = $_POST["formData"];
		
		// Check Formular-Daten
		$checkEmptyFields = array(
			$lng->txt("message")   => $formData["message"]	
		);
		$errors = TUtil::checkFormEmpty($checkEmptyFields);
		if ($errors != "")
		{
			$tpl->setVariable("TXT_FORM_FEEDBACK", $lng->txt("form_empty_fields")."<br>".$errors);
		}
		else
		{			
			$newPost = $frm->generatePost($_GET["obj_id"], $_GET["parent"], $topicData["top_pk"], $_GET["thr_pk"], $_SESSION["AccountId"], $formData["message"], $_GET["pos_pk"]);
			
			$tpl->setVariable("TXT_FORM_FEEDBACK", $lng->txt("forums_post_new_entry"));
		}
	}
	
	// Hole 1.Post des Threads
	$first_node = $frm->getFirstPostNode($_GET["thr_pk"]);	
	// Hole gesamten Tree des Threads
	$frm->setOrderField($orderField);
	$subtree_nodes = $frm->getPostTree($first_node);
	$posNum = count($subtree_nodes);
	$pageHits = $frm->getPageHits();
	
	$z = 0;
	
	// Navigation zum Blättern der Seiten
	if ($posNum > $pageHits)
	{
		$params = array(
			"obj_id"		=> $_GET["obj_id"],	
			"parent"		=> $_GET["parent"],
			"thr_pk"		=> $_GET["thr_pk"],		
			"orderby"		=> $_GET["orderby"]
		);
		
		if (!$_GET["offset"]) $Start = 0;
		else $Start = $_GET["offset"];
		
		$linkbar = TUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$posNum,$pageHits,$Start,$params);
		
		if ($linkbar != "")
			$tpl->setVariable("LINKBAR", $linkbar);
	}
	
	foreach($subtree_nodes as $node)
	{
		
		if ($posNum > $pageHits && $z >= ($Start+$pageHits))
			break;
		
		if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)
		{
		
			// Auf Post antworten
			if ($rbacsystem->checkAccess("write", $_GET["obj_id"], $_GET["parent"])) 
			{
				if ($_GET["cmd"] == "showreply" && $_GET["pos_pk"] == $node["pos_pk"])
				{
					$tpl->setCurrentBlock("reply_post");
					$tpl->setVariable("FORM_ANKER", $_GET["pos_pk"]);
					$tpl->setVariable("TXT_FORM_HEADER", $lng->txt("forums_your_reply"));
					$tpl->setVariable("TXT_FORM_MESSAGE", $lng->txt("forums_the_post"));
					$tpl->setVariable("FORM_MESSAGE", "[quote]".$node["message"]."[/quote]");
					$tpl->setVariable("SUBMIT", $lng->txt("submit"));
					$tpl->setVariable("RESET", $lng->txt("reset"));
					$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=replypost&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&pos_pk=".$_GET["pos_pk"]."&thr_pk=".$_GET["thr_pk"]."&offset=".$Start."&orderby=".$_GET["orderby"]);
					$tpl->parseCurrentBlock("reply_post");
				}
				else
				{			
					$tpl->setCurrentBlock("reply_cell");
					$tpl->setVariable("SPACER","<hr noshade width=100% size=1 align='center'>"); 
					$tpl->setVariable("REPLY_BUTTON","<a href=\"forums_threads_view.php?cmd=showreply&pos_pk=".$node["pos_pk"]."&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&offset=".$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]."\">".$lng->txt("reply")."</a>"); 
					$tpl->parseCurrentBlock("reply_cell");
					$tpl->setVariable("POST_ANKER", $node["pos_pk"]);
				}
			}
			else $tpl->setVariable("POST_ANKER", $node["pos_pk"]);
			
			$tpl->setCurrentBlock("posts_row");
			$rowCol = TUtil::switchColor($z,"tblrow2","tblrow1");
			$tpl->setVariable("ROWCOL", $rowCol);
			
			// Hole User-Daten		
			unset($author);
			$author = $frm->getModerator($node["author"]);	
			$tpl->setVariable("AUTHOR","<a href=\"forums_user_view?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&user=".$node["author"]."&backurl=forums_threads_view&offset=".$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."\">".$author["SurName"]."</a>"); 
			
			if ($node["update"] != $node["create_date"]) {
				$node["update"] = $frm->convertDate($node["update"]);
				$tpl->setVariable("POST_UPDATE",$lng->txt("edited_at").": ".$node["update"]);
			}		
			$node["create_date"] = $frm->convertDate($node["create_date"]);
			$tpl->setVariable("POST_DATE",$node["create_date"]);	
			
			$node["message"] = $frm->prepareText($node["message"]);
			
			$tpl->setVariable("POST",$node["message"]);	
			
			$tpl->parseCurrentBlock("posts_row");		
		
		}			
		
		$z ++;		
	}
		
}
else
{
	$tpl->setCurrentBlock("posts_no");
	$tpl->setVAriable("TXT_MSG_NO_POSTS_AVAILABLE",$lng->txt("forums_posts_not_available"));
	$tpl->parseCurrentBlock("posts_no");
}

//$posPath = $frm->getForumPath($_GET["obj_id"], $_GET["parent"], $topicData["top_pk"], $_GET["thr_pk"]);

$tpl->setCurrentBlock("posttable");
//$tpl->setVariable("TXT_POST_PATH", $posPath);
$tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
$tpl->setVariable("TXT_POST", $lng->txt("forums_the_post"));
$tpl->parseCurrentBlock("posttable");


if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
	$tpl->setCurrentBlock("message2");
	$tpl->setVariable("MSG", urldecode( $_GET["message"]));
	$tpl->parseCurrentBlock();
}


$tpl->show();

?>