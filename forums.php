<?php
/**
* forums
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.Forum.php";

$frm = new Forum();

$tpl->addBlockFile("CONTENT", "content", "tpl.forums.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

// get all forums
$frm_obj = TUtil::getObjectsByOperations('frm','visible');
$frmNum = count($frm_obj);

$pageHits = $frm->getPageHits();

if ($frmNum > 0)
{
	$z = 0;	
	
	// navigation to browse
	if ($frmNum > $pageHits)
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
		
		$linkbar = TUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$frmNum,$pageHits,$Start,$params);
		
		if ($linkbar != "")
		{
			$tpl->setVariable("LINKBAR", $linkbar);
		}
	}	
		
	// get forums dates
	foreach($frm_obj as $data)
	{		
		if ($frmNum > $pageHits && $z >= ($Start+$pageHits))
		{
			break;
		}
		
		if (($frmNum > $pageHits && $z >= $Start) || $frmNum <= $pageHits)
		{
		
			unset($topicData);
			
			$frm->setWhereCondition("top_frm_fk = ".$data["obj_id"]);			
			$topicData = $frm->getOneTopic();		
			
			if ($topicData["top_num_threads"] > 0)
			{
				$thr_page = "liste";
			}
			else
			{
				$thr_page = "new";
			}
		
			$tpl->setCurrentBlock("forum_row");
			$rowCol = TUtil::switchColor($z,"tblrow2","tblrow1");
			$tpl->setVariable("ROWCOL", $rowCol);		
			
			$moderators = "";		
			$lpCont = "";
			$lastPost = "";
			
			// get last-post data
			if ($topicData["top_last_post"] != "")
			{
				$lastPost = $frm->getLastPost($topicData["top_last_post"]);
				$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
			}
			// read-access
			// TODO: this will not work :-(
			// We have no ref_id at this point
			if ($rbacsystem->checkAccess("read", $data["ref_id"])) 
			{			
				// forum title
				if ($topicData["top_num_threads"] < 1 && (!$rbacsystem->checkAccess("write", $data["ref_id"])))
				{
					$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");
				}
				else
				{
					$tpl->setVariable("TITLE","<a href=\"forums_threads_".$thr_page.".php?ref_id=".$data["ref_id"]."&backurl=forums\">".$topicData["top_name"]."</a>");
				}
				// create-dates of forum
				if ($topicData["top_usr_id"] > 0)
				{			
					$moderator = $frm->getUser($topicData["top_usr_id"]);	
					
					$tpl->setVariable("START_DATE_TXT1", $lng->txt("launch"));
					$tpl->setVariable("START_DATE_TXT2", $lng->txt("by"));
					$tpl->setVariable("START_DATE", $frm->convertDate($topicData["top_date"]));
					$tpl->setVariable("START_DATE_USER","<a href=\"forums_user_view.php?ref_id=".$data["ref_id"]."&user=".$topicData["top_usr_id"]."&backurl=forums&offset=".$Start."\">".$moderator->getLastName()."</a>"); 										
				}
				
				// when forum was changed ...
				if ($topicData["update_user"] > 0)
				{			
					$moderator = $frm->getUser($topicData["update_user"]);	
					
					$tpl->setVariable("LAST_UPDATE_TXT1", $lng->txt("last_change"));
					$tpl->setVariable("LAST_UPDATE_TXT2", $lng->txt("by"));
					$tpl->setVariable("LAST_UPDATE", $frm->convertDate($topicData["top_update"]));
					$tpl->setVariable("LAST_UPDATE_USER","<a href=\"forums_user_view.php?ref_id=".$data["ref_id"]."&user=".$topicData["update_user"]."&backurl=forums&offset=".$Start."\">".$moderator->getLastName()."</a>"); 										
				}
				
				// show content of last-post
				if (is_array($lastPost))
				{					
					$lpCont = "<a href=\"forums_threads_view.php?pos_pk=".$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&ref_id=".$data["ref_id"]."#".$lastPost["pos_pk"]."\">".$lastPost["pos_message"]."</a><br>".$lng->txt("from")."&nbsp;";			
					$lpCont .= "<a href=\"forums_user_view.php?ref_id=".$data["ref_id"]."&user=".$lastPost["pos_usr_id"]."&backurl=forums&offset=".$Start."\">".$lastPost["lastname"]."</a><br>";
					$lpCont .= $lastPost["pos_date"];							
				}
	
				$tpl->setVariable("LAST_POST", $lpCont);
				
				// get dates of moderators
				if ($topicData["top_mods"] > 0)
				{			
					$MODS = $rbacreview->assignedUsers($topicData["top_mods"]);
												
					for ($i = 0; $i < count($MODS); $i++)
					{
						unset($moderator);						
						$moderator = $frm->getUser($MODS[$i]);
						
						if ($moderators != "")
						{
							$moderators .= ", ";
						}

						$moderators .= "<a href=\"forums_user_view.php?ref_id=".$data["ref_id"]."&user=".$MODS[$i]."&backurl=forums&offset=".$Start."\">".$moderator->getLastName()."</a>";
					}
				}							
				$tpl->setVariable("MODS",$moderators); 
		
			}
			else 
			{
				// only visible-access	
				$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");
				
				if (is_array($lastPost))
				{
					$lpCont = $lastPost["pos_message"]."<br>".$lng->txt("from")." ".$lastPost["lastname"]."<br>".$lastPost["pos_date"];				
				}

				$tpl->setVariable("LAST_POST", $lpCont);
				
				if ($topicData["top_mods"] > 0)
				{			
					$MODS = $rbacreview->assignedUsers($topicData["top_mods"]);
										
					for ($i = 0; $i < count($MODS); $i++)
					{
						unset($moderator);
						$moderator = $frm->getUser($MODS[$i]);
						
						if ($moderators != "")
						{
							$moderators .= ", ";
						}
						
						$moderators .= $moderator->getLastName();
					}
				}
				$tpl->setVariable("MODS",$moderators); 
			}		
			
			// get context of forum			
			$PATH = $frm->getForumPath($data["ref_id"]);
			$tpl->setVariable("FORUMPATH",$PATH);
			
			$tpl->setVariable("DESCRIPTION",$topicData["top_description"]);
			$tpl->setVariable("NUM_THREADS",$topicData["top_num_threads"]);
			$tpl->setVariable("NUM_POSTS",$topicData["top_num_posts"]);		
			$tpl->setVariable("NUM_VISITS",$topicData["visits"]);		
		
			$tpl->parseCurrentBlock("forum_row");			
		}
		
		$z ++;		
	}	
}
else
{
	$tpl->setCurrentBlock("forum_no");
	$tpl->setVAriable("TXT_MSG_NO_FORUMS_AVAILABLE",$lng->txt("forums_not_available"));
	$tpl->parseCurrentBlock("forum_no");
}

$tpl->setCurrentBlock("forum");

if ($_GET["feedback"] != "")
{
	$tpl->setVariable("TXT_FEEDBACK", $_GET["feedback"]);
}

$tpl->setVariable("COUNT_FORUM", $lng->txt("forums_count").": ".$frmNum);
$tpl->setVariable("TXT_FORUM_GROUP", $lng->txt("forums_overview"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
$tpl->setVariable("TXT_NUM_THREADS", $lng->txt("forums_threads"));
$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_articles"));
$tpl->setVariable("TXT_NUM_VISITS", $lng->txt("visits"));
$tpl->setVariable("TXT_LAST_POST", $lng->txt("forums_last_post"));
$tpl->setVariable("TXT_MODS", $lng->txt("forums_moderators"));
$tpl->setVariable("TXT_FORUMPATH", $lng->txt("context"));
$tpl->parseCurrentBlock("forum");

if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
	$tpl->setCurrentBlock("message2");
	$tpl->setVariable("MSG", urldecode( $_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tpl->show();
?>