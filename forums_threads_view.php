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
require_once "classes/class.ilObjForum.php";

$lng->loadLanguageModule("forum");

$forumObj = new ilObjForum($_GET["ref_id"]);
$frm = new ilForum();
$frm->setForumId($forumObj->getId());

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_view.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
// catch stored message
sendInfo();
// display infopanel if something happened
infoPanel();

if (!$rbacsystem->checkAccess("read", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->setVariable("TXT_FORUM_ARTICLES", $lng->txt("forums_posts"));

// sorting val for posts
if ($_GET["orderby"] == "")
{
	$old_order = "answers";
}
else
{
	$old_order = $_GET["orderby"];
}

if ($old_order == "date")
{
	$new_order = "answers";
	$orderField = "frm_posts_tree.date";
}
else
{
	$new_order = "date";
	$orderField = "frm_posts_tree.rgt";
}

$tpl->setVariable("LINK_SORT", "<b>></b><a href=\"forums_threads_view.php?orderby=".$new_order."&thr_pk=".$_GET["thr_pk"]."&ref_id=".$_GET["ref_id"]."\">".$lng->txt("order_by")." ".$lng->txt($new_order)."</a>");

// get forum- and thread-data
$frm->setWhereCondition("top_frm_fk = ".$frm->getForumId());

if (is_array($topicData = $frm->getOneTopic()))
{
	$frm->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
	$threadData = $frm->getOneThread();

	$tpl->setVariable("TXT_PAGEHEADLINE", $threadData["thr_subject"]);

	// Visit-Counter
	$frm->setDbTable("frm_threads");
	$frm->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
	$frm->updateVisits($_GET["thr_pk"]);

	// ********************************************************************************
	// build location-links
	$tpl->touchBlock("locator_separator");
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_overview"));
	$tpl->setVariable("LINK_ITEM", "forums.php?ref_id=".$_GET["ref_id"]);
	$tpl->setVariable("LINK_TARGET","target=\"bottom\"");
	$tpl->parseCurrentBlock();

	$tpl->touchBlock("locator_separator");
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_topics_overview").": ".$topicData["top_name"]);
	$tpl->setVariable("LINK_ITEM", "forums_threads_liste.php?ref_id=".$_GET["ref_id"]);
	$tpl->setVariable("LINK_TARGET","target=\"bottom\"");
	$tpl->parseCurrentBlock();
		
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_thread_articles").": ".$threadData["thr_subject"]);
	$tpl->setVariable("LINK_ITEM", "forums_threads_view.php?thr_pk=".$_GET["thr_pk"]."&ref_id=".$_GET["ref_id"]);
	$tpl->parseCurrentBlock();
	
	// TREEVIEW <-> FLATVIEW
	if (!isset($_SESSION["viewmode"]) or $_SESSION["viewmode"] == "flat")
	{
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK","forums_frameset.php?viewmode=tree&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
		$tpl->setVariable("BTN_TXT", $lng->txt("treeview"));
		$tpl->parseCurrentBlock();
	}
	else
	{
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK","forums_frameset.php?viewmode=flat&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
		$tpl->setVariable("BTN_TARGET","target=\"_parent\"");
		$tpl->setVariable("BTN_TXT", $lng->txt("flatview"));
		$tpl->parseCurrentBlock();
	}
	
	if ($rbacsystem->checkAccess("write", $_GET["ref_id"]))
	{
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK","forums_threads_new.php?ref_id=".$_GET["ref_id"]);
		$tpl->setVariable("BTN_TARGET","target=\"bottom\"");
		$tpl->setVariable("BTN_TXT", $lng->txt("forums_new_thread"));
		$tpl->parseCurrentBlock();
	}
	else
	{
		$tpl->setVariable("NO_BTN", "<br/><br/>"); 
	}
	// ********************************************************************************
	
	// form processing (edit & reply)
	if ($_GET["cmd"] == "ready_showreply" || $_GET["cmd"] == "ready_showedit")
	{		
		$formData = $_POST["formData"];
		
		// check form-dates
		$checkEmptyFields = array(
			$lng->txt("message")   => $formData["message"]	
		);
		
		$errors = ilUtil::checkFormEmpty($checkEmptyFields);

		if ($errors != "")
		{
			sendInfo($lng->txt("form_empty_fields")." ".$errors);
		}
		else
		{			
			if ($_GET["cmd"] == "ready_showreply")
			{
				// reply: new post
				$newPost = $frm->generatePost($topicData["top_pk"], $_GET["thr_pk"], $_SESSION["AccountId"], $formData["message"], $_GET["pos_pk"]);			
				sendInfo($lng->txt("forums_post_new_entry"));
			}
			else
			{				
				// edit: update post
				if ($frm->updatePost($formData["message"], $_GET["pos_pk"]))
				{
					sendInfo($lng->txt("forums_post_modified"));
				}
			}
		}
	}
	
	// delete post and its sub-posts
/*
	if ($_GET["cmd"] == "ready_delete" && $_POST["confirm"] != "")
	{
		$dead_thr = $frm->deletePost($_GET["pos_pk"]);		
		
		// if complete thread was deleted ...
		if ($dead_thr == $_GET["thr_pk"])
		{
			sendInfo($lng->txt("forums_post_deleted"),true);
			header("location: forums.php?ref_id=".$_GET["ref_id"]);
			exit();
		}
		
		sendInfo($lng->txt("forums_post_deleted"));
	}
*/	
	// get first post of thread
	$first_node = $frm->getFirstPostNode($_GET["thr_pk"]);	
	
	// get complete tree of thread
	$frm->setOrderField($orderField);
	$subtree_nodes = $frm->getPostTree($first_node);
	$posNum = count($subtree_nodes);
	
	$pageHits = $frm->getPageHits();
	
	$z = 0;
	
	// navigation to browse
	if ($posNum > $pageHits)
	{
		$params = array(
			"ref_id"		=> $_GET["ref_id"],	
			"thr_pk"		=> $_GET["thr_pk"],		
			"orderby"		=> $_GET["orderby"]
		);
		
		if (!$_GET["offset"])
		{
			$Start = 0;
		}
		else
		{
			$Start = $_GET["offset"];
		}
		
		$linkbar = ilUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$posNum,$pageHits,$Start,$params);
		
		if ($linkbar != "")
		{
			$tpl->setVariable("LINKBAR", $linkbar);
		}
	}
	
	// assistance val for anchor-links
	$jump = 0;
	
	// generate post-dates
	foreach($subtree_nodes as $node)
	{
		if ($_GET["pos_pk"] && $_GET["pos_pk"] == $node["pos_pk"])
		{
			$jump ++;
		}
		
		if ($posNum > $pageHits && $z >= ($Start+$pageHits))
		{
			// if anchor-link was not found ...
			if ($_GET["pos_pk"] && $jump < 1)
			{
				header("location: forums_threads_view.php?thr_pk=".$_GET["thr_pk"]."&ref_id=".$_GET["ref_id"]."&pos_pk=".$_GET["pos_pk"]."&offset=".($Start+$pageHits)."&orderby=".$_GET["orderby"]);
				exit();
			}
			else
			{
				break;
			}
		}
		
		if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)
		{
			if ($rbacsystem->checkAccess("write", $_GET["ref_id"])) 
			{
				// reply/edit
				if (($_GET["cmd"] == "showreply" || $_GET["cmd"] == "showedit") && $_GET["pos_pk"] == $node["pos_pk"])
				{
					$tpl->setCurrentBlock("reply_post");
					$tpl->setVariable("REPLY_ANKER", $_GET["pos_pk"]);
					
					if ($_GET["cmd"] == "showreply")
					{
						$tpl->setVariable("TXT_FORM_HEADER", $lng->txt("forums_your_reply"));
					}
					else
					{
						$tpl->setVariable("TXT_FORM_HEADER", $lng->txt("forums_edit_post"));
					}
	
					$tpl->setVariable("TXT_FORM_MESSAGE", $lng->txt("forums_the_post"));

					if ($_GET["cmd"] == "showreply")
					{
						$tpl->setVariable("FORM_MESSAGE", $frm->prepareText($node["message"],1));
					}
					else
					{
						$tpl->setVariable("FORM_MESSAGE", $frm->prepareText($node["message"],2));
					}

					$tpl->setVariable("SUBMIT", $lng->txt("submit"));
					$tpl->setVariable("RESET", $lng->txt("reset"));
					$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=ready_".$_GET["cmd"]."&ref_id=".$_GET["ref_id"]."&pos_pk=".$_GET["pos_pk"]."&thr_pk=".$_GET["thr_pk"]."&offset=".$Start."&orderby=".$_GET["orderby"]);
					$tpl->parseCurrentBlock("reply_post");
					
				} // if (($_GET["cmd"] == "showreply" || $_GET["cmd"] == "showedit") && $_GET["pos_pk"] == $node["pos_pk"])
				else
				{						
					// button: delete article
					if ($rbacsystem->checkAccess("delete post", $_GET["ref_id"]))
					{
						// 2. delete-level
						if ($_GET["cmd"] == "delete" && $_GET["pos_pk"] == $node["pos_pk"])
						{
							$tpl->setCurrentBlock("kill_cell");
							$tpl->setVariable("KILL_ANKER", $_GET["pos_pk"]);
							$tpl->setVariable("KILL_SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">"); 
							$tpl->setVariable("TXT_KILL", $lng->txt("forums_info_delete_post"));								
//							$tpl->setVariable("DEL_FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=ready_delete&ref_id=".$_GET["ref_id"]."&pos_pk=".$node["pos_pk"]."&thr_pk=".$_GET["thr_pk"]."&offset=".$Start."&orderby=".$_GET["orderby"]);							
							$tpl->setVariable("DEL_FORMACTION", "forums_frameset.php?cmd=ready_delete&ref_id=".$_GET["ref_id"]."&pos_pk=".$node["pos_pk"]."&thr_pk=".$_GET["thr_pk"]."&offset=".$Start."&orderby=".$_GET["orderby"]);
							$tpl->setVariable("CANCEL_BUTTON", $lng->txt("cancel")); 
							$tpl->setVariable("CONFIRM_BUTTON", $lng->txt("confirm")); 
							$tpl->parseCurrentBlock("kill_cell");
						}
						else
						{
							// 1. delete-level
							$tpl->setCurrentBlock("del_cell");
							$tpl->setVariable("DEL_BUTTON","<a href=\"forums_threads_view.php?cmd=delete&pos_pk=".$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]."\">".$lng->txt("delete")."</a>"); 
							$tpl->parseCurrentBlock("del_cell");
						}
					}
					
					if (($_GET["cmd"] != "delete") || ($_GET["cmd"] == "delete" && $_GET["pos_pk"] != $node["pos_pk"]))
					{
						// button: edit article
						if ($frm->checkEditRight($node["pos_pk"]))
						{
							$tpl->setCurrentBlock("edit_cell");
							$tpl->setVariable("EDIT_BUTTON","<a href=\"forums_threads_view.php?cmd=showedit&pos_pk=".$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]."\">".$lng->txt("edit")."</a>"); 
							$tpl->parseCurrentBlock("edit_cell");
						}
						
						// button: print
						$tpl->setCurrentBlock("print_cell");
						$tpl->setVariable("SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">"); 
						$tpl->setVariable("PRINT_BUTTON","<a href=\"forums_export.php?&print_post=".$node["pos_pk"]."&top_pk=".$topicData["top_pk"]."&thr_pk=".$threadData["thr_pk"]."\" target=\"_blank\">".$lng->txt("print")."</a>"); 
						$tpl->parseCurrentBlock("print_cell");
						
						// button: reply
						$tpl->setCurrentBlock("reply_cell");
						$tpl->setVariable("SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">"); 
						$tpl->setVariable("REPLY_BUTTON","<a href=\"forums_threads_view.php?cmd=showreply&pos_pk=".$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]."\">".$lng->txt("reply")."</a>"); 
						$tpl->parseCurrentBlock("reply_cell");
												
						$tpl->setVariable("POST_ANKER", $node["pos_pk"]);		
					}			
				} // else
				
			} // if ($rbacsystem->checkAccess("write", $_GET["ref_id"])) 
			else
			{
				$tpl->setVariable("POST_ANKER", $node["pos_pk"]);
			}
			
			$tpl->setCurrentBlock("posts_row");
			$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
			$tpl->setVariable("ROWCOL", $rowCol);
			
			// get author data
			unset($author);
			$author = $frm->getUser($node["author"]);	
			$tpl->setVariable("AUTHOR","<a href=\"forums_user_view.php?ref_id=".$_GET["ref_id"]."&user=".$node["author"]."&backurl=forums_threads_view&offset=".$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."\">".$author->getLogin()."</a>"); 
			
			// get create- and update-dates
			if ($node["update_user"] > 0)
			{
				$node["update"] = $frm->convertDate($node["update"]);
				unset($lastuser);
				$lastuser = $frm->getUser($node["update_user"]);					
				$tpl->setVariable("POST_UPDATE","<br/>[".$lng->txt("edited_at").": ".$node["update"]." - ".strtolower($lng->txt("from"))." ".$lastuser->getLogin()."]");
			}

			$tpl->setVariable("TXT_REGISTERED", $lng->txt("registered_since"));
			$tpl->setVariable("REGISTERED_SINCE",$frm->convertDate($author->getCreateDate()));

			$numPosts = $frm->countUserArticles($author->id);
			$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_posts"));
			$tpl->setVariable("NUM_POSTS",$numPosts);
			
			// prepare post
			$node["message"] = $frm->prepareText($node["message"]);
			
			// make links in post usable 
			$node["message"] = ilUtil::makeClickable($node["message"]);

			$tpl->setVariable("TXT_CREATE_DATE",$lng->txt("forums_thread_create_date"));
			$tpl->setVariable("POST_DATE",$frm->convertDate($node["create_date"]));
			$tpl->setVariable("SPACER","<hr noshade width=100% size=1 align='center'>");			
			$tpl->setVariable("POST",nl2br($node["message"]));	
			$tpl->parseCurrentBlock("posts_row");	
				
		} // if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)

		$z ++;	
			
	} // foreach($subtree_nodes as $node)
}
else
{
	$tpl->setCurrentBlock("posts_no");
	$tpl->setVAriable("TXT_MSG_NO_POSTS_AVAILABLE",$lng->txt("forums_posts_not_available"));
	$tpl->parseCurrentBlock("posts_no");
}

$tpl->setCurrentBlock("posttable");
$tpl->setVariable("COUNT_POST", $lng->txt("forums_count_art").": ".$posNum);
$tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
$tpl->setVariable("TXT_POST", $lng->txt("forums_thread").": ".$threadData["thr_subject"]);

$tpl->parseCurrentBlock("posttable");

$tpl->show();
?>
