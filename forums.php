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

//$_SESSION["backurl"] = "forums";

$frm = new Forum();

$tpl->addBlockFile("CONTENT", "content", "tpl.forums.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$frm_obj = TUtil::getObjectsByOperations('frm','visible');
$frmNum = count($frm_obj);
$pageHits = $frm->getPageHits();

if ($frmNum > 0)
{
	$z = 0;	
	
	// Navigation zum Blättern der Seiten
	if ($frmNum > $pageHits)
	{
		$params = array(
			"obj_id"		=> $_GET["obj_id"],	
			"parent"		=> $_GET["parent"]		
		);
		
		if (!$_GET["offset"]) $Start = 0;
		else $Start = $_GET["offset"];
		
		$linkbar = TUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$frmNum,$pageHits,$Start,$params);
		
		if ($linkbar != "")
			$tpl->setVariable("LINKBAR", $linkbar);
	}	
		
	
	foreach($frm_obj as $data)
	{		
		if ($frmNum > $pageHits && $z >= ($Start+$pageHits))
			break;
		
		if (($frmNum > $pageHits && $z >= $Start) || $frmNum <= $pageHits)
		{
		
			unset($topicData);
			
			$frm->setWhereCondition("top_frm_fk = ".$data["obj_id"]);			
			$topicData = $frm->getOneTopic();		
			
			if ($topicData["top_num_threads"] > 0) $thr_page = "liste";
			else $thr_page = "new";
					
			$tpl->setCurrentBlock("forum_row");
			$rowCol = TUtil::switchColor($z,"tblrow2","tblrow1");
			$tpl->setVariable("ROWCOL", $rowCol);		
			
			$moderators = "";		
			$lpCont = "";
			$lastPost = "";
					
			if ($topicData["top_last_post"] != "") {
				$lastPost = $frm->getLastPost($topicData["top_last_post"]);
				$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
			}
					
			if ($rbacsystem->checkAccess("read", $data["obj_id"], $data["parent"])) 
			{			
				if ($topicData["top_num_threads"] < 1 && (!$rbacsystem->checkAccess("write", $data["obj_id"], $data["parent"]))) {
					$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");
				}
				else $tpl->setVariable("TITLE","<a href=\"forums_threads_".$thr_page.".php?obj_id=".$data["obj_id"]."&parent=".$data["parent"]."&backurl=forums\">".$topicData["top_name"]."</a>");
				
				if (is_array($lastPost)) {					
					$lpCont = "<a href=\"forums_threads_view.php?pos_pk=".$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&obj_id=".$data["obj_id"]."&parent=".$data["parent"]."#".$lastPost["pos_pk"]."\">".$lastPost["pos_message"]."</a><br>".$lng->txt("from")."&nbsp;";			
					$lpCont .= "<a href=\"forums_user_view?obj_id=".$data["obj_id"]."&parent=".$data["parent"]."&user=".$lastPost["pos_usr_id"]."&backurl=forums&offset=".$Start."\">".$lastPost["surname"]."</a><br>";
					$lpCont .= $lastPost["pos_date"];							
				}
				$tpl->setVariable("LAST_POST", $lpCont);
				
				if ($topicData["top_mods"] != "")
				{			
					$MODS = explode("#", $topicData["top_mods"]);
					for ($i = 0; $i < count($MODS); $i++)
					{
						unset($modData);
						$modData = $frm->getModerator($MODS[$i]);	
						if ($moderators != "") $moderators .= ", ";
						$moderators .= "<a href=\"forums_user_view?obj_id=".$data["obj_id"]."&parent=".$data["parent"]."&user=".$MODS[$i]."&backurl=forums&offset=".$Start."\">".$modData["SurName"]."</a>";
					}
				}
							
				$tpl->setVariable("MODS",$moderators); 
				
			}
			else 
			{
				$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");
				
				if (is_array($lastPost)) {
					$lpCont = $lastPost["pos_message"]."<br>".$lng->txt("from")." ".$lastPost["surname"]."<br>".$lastPost["pos_date"];				
				}
				$tpl->setVariable("LAST_POST", $lpCont);
				
				if ($topicData["top_mods"] != "")
				{			
					$MODS = explode("#", $topicData["top_mods"]);
					for ($i = 0; $i < count($MODS); $i++)
					{
						unset($modData);
						$modData = $frm->getModerator($MODS[$i]);	
						if ($moderators != "") $moderators .= ", ";
						$moderators .= $modData["SurName"];
					}
				}
				$tpl->setVariable("MODS",$moderators); 
			}		
			
			if ($data["obj_id"] != $objID && $data["parent"] != $parentID)
			{		
				$PATH = $frm->getForumPath($data["obj_id"], $data["parent"]);
			}
			$tpl->setVariable("FORUMPATH",$PATH);
			
			$tpl->setVariable("DESCRIPTION",$topicData["top_description"]);
			$tpl->setVariable("NUM_THREADS",$topicData["top_num_threads"]);
			$tpl->setVariable("NUM_POSTS",$topicData["top_num_posts"]);		
			$tpl->setVariable("NUM_VISITS",$topicData["visits"]);		
			
	        $tpl->parseCurrentBlock("forum_row");
			
			$objID = $data["obj_id"];
			$parentID = $data["parent"];
			
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