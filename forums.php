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
* forums
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilForum.php";

switch($_GET["cmd"])
{
	case "addToDesk":
		$ilias->account->addDesktopItem($_GET["item_id"], "frm");
		break;
}

$frm = new ilForum();

$lng->loadLanguageModule("forum");

$tpl->addBlockFile("CONTENT", "content", "tpl.forums.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

// set locator
$tpl->setVariable("TXT_LOCATOR",$lng->txt("locator"));
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("forums_overview"));
$tpl->setVariable("LINK_ITEM", "forums.php");
$tpl->parseCurrentBlock();

// display buttons
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","obj_location_new.php?new_type=frm&from=".basename($PATH_INFO));
$tpl->setVariable("BTN_TXT",$lng->txt("frm_new"));
$t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "frm");
$tpl->setVariable("BTN_TARGET","target=\"$t_frame\"");
$tpl->parseCurrentBlock();

// catch stored message
sendInfo();
// display infopanel if something happened
infoPanel();

// get all forums
$frm_obj = ilUtil::getObjectsByOperations('frm','visible');
$frmNum = count($frm_obj);

$pageHits = $frm->getPageHits();

// start: form operations
if (isset($_POST["cmd"]["submit"]))
{	
	if(is_array($_POST["forum_id"]))
	{
		$startTbl = "frm_data";
		
		require_once "forums_export.php";	
		
		unset($topicData);	
		
	}
	
}
// end: form operations

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
		
		$linkbar = ilUtil::Linkbar(basename($_SERVER["PHP_SELF"]),$frmNum,$pageHits,$Start,$params);
		
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
			$tpl->setVariable("TXT_FORUMPATH", $lng->txt("context"));
		
			$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
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
				if (($topicData["top_num_threads"] < 1)  && (!$rbacsystem->checkAccess("edit_post", $data["ref_id"])))
				{
					$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");
				}
				else
				{
					$tpl->setVariable("TITLE","<a href=\"forums_threads_".$thr_page.".php?ref_id=".$data["ref_id"]."&backurl=forums\">".$topicData["top_name"]."</a>");
				}
				// add to desktop link
				if (!$ilias->account->isDesktopItem($data["ref_id"], "frm"))
				{
					$tpl->setVariable("TO_DESK_LINK", "forums.php?cmd=addToDesk&item_id=".$data["ref_id"]);
					$tpl->setVariable("TXT_TO_DESK", "(".$lng->txt("to_desktop").")");
				}
				// create-dates of forum
				if ($topicData["top_usr_id"] > 0)
				{
					$moderator = $frm->getUser($topicData["top_usr_id"]);

					$tpl->setVariable("START_DATE_TXT1", $lng->txt("launch"));
					$tpl->setVariable("START_DATE_TXT2", strtolower($lng->txt("by")));
					$tpl->setVariable("START_DATE", $frm->convertDate($topicData["top_date"]));
					$tpl->setVariable("START_DATE_USER","<a href=\"forums_user_view.php?ref_id=".$data["ref_id"]."&user=".$topicData["top_usr_id"]."&backurl=forums&offset=".$Start."\">".$moderator->getLogin()."</a>"); 										
				}
				
				// when forum was changed ...
				if ($topicData["update_user"] > 0)
				{			
					$moderator = $frm->getUser($topicData["update_user"]);	
					
					$tpl->setVariable("LAST_UPDATE_TXT1", $lng->txt("last_change"));
					$tpl->setVariable("LAST_UPDATE_TXT2", strtolower($lng->txt("by")));
					$tpl->setVariable("LAST_UPDATE", $frm->convertDate($topicData["top_update"]));
					$tpl->setVariable("LAST_UPDATE_USER","<a href=\"forums_user_view.php?ref_id=".$data["ref_id"]."&user=".$topicData["update_user"]."&backurl=forums&offset=".$Start."\">".$moderator->getLogin()."</a>"); 										
				}
				
				// show content of last-post
				if (is_array($lastPost))
				{					
					$lpCont = "<a href=\"forums_frameset.php?target=true&pos_pk=".$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&ref_id=".$data["ref_id"]."#".$lastPost["pos_pk"]."\">".$lastPost["pos_message"]."</a><br/>".strtolower($lng->txt("from"))."&nbsp;";			
					$lpCont .= "<a href=\"forums_user_view.php?ref_id=".$data["ref_id"]."&user=".$lastPost["pos_usr_id"]."&backurl=forums&offset=".$Start."\">".$lastPost["login"]."</a><br/>";
					$lpCont .= $lastPost["pos_date"];							
				}
	
				$tpl->setVariable("LAST_POST", $lpCont);
				
				// get dates of moderators
				$MODS = $frm->getModerators();
											
				for ($i = 0; $i < count($MODS); $i++)
				{
					unset($moderator);						
					$moderator = $frm->getUser($MODS[$i]);
					
					if ($moderators != "")
					{
						$moderators .= ", ";
					}

					$moderators .= "<a href=\"forums_user_view.php?ref_id=".$data["ref_id"]."&user=".$MODS[$i]."&backurl=forums&offset=".$Start."\">".$moderator->getLogin()."</a>";
				}
					
				$tpl->setVariable("MODS",$moderators); 
				
				$tpl->setVariable("FORUM_ID", $topicData["top_pk"]);				
				
		
			} // if ($rbacsystem->checkAccess("read", $data["ref_id"])) 
			else 
			{
				// only visible-access	
				$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");
				
				if (is_array($lastPost))
				{
					$lpCont = $lastPost["pos_message"]."<br/>".$lng->txt("from")." ".$lastPost["lastname"]."<br/>".$lastPost["pos_date"];				
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
						
						$moderators .= $moderator->getLogin();
					}
				}
				$tpl->setVariable("MODS",$moderators); 
			} // else	
			
			// get context of forum			
			$PATH = $frm->getForumPath($data["ref_id"]);
			$tpl->setVariable("FORUMPATH",$PATH);
			
			$tpl->setVariable("DESCRIPTION",$topicData["top_description"]);
			$tpl->setVariable("NUM_THREADS",$topicData["top_num_threads"]);
			$tpl->setVariable("NUM_POSTS",$topicData["top_num_posts"]);		
			$tpl->setVariable("NUM_VISITS",$topicData["visits"]);		
		
			$tpl->parseCurrentBlock();	
					
		} // if (($frmNum > $pageHits && $z >= $Start) || $frmNum <= $pageHits)
		
		$z ++;		
		
	} // foreach($frm_obj as $data)

	$tpl->setCurrentBlock("forum_options");	
	$tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
	$tpl->setVariable("IMGPATH",$tpl->tplPath);
	$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?ref_id=".$_GET["ref_id"]);
	$tpl->setVariable("TXT_OK",$lng->txt("ok"));	
	//$tpl->setVariable("TXT_PRINT", $lng->txt("print"));
	$tpl->setVariable("TXT_EXPORT_HTML", $lng->txt("export_html"));
	$tpl->setVariable("TXT_EXPORT_XML", $lng->txt("export_xml"));
	$tpl->parseCurrentBlock();
	
} // if ($frmNum > 0)
else
{
	$tpl->setCurrentBlock("forum_no");
	$tpl->setVariable("TXT_MSG_NO_FORUMS_AVAILABLE",$lng->txt("forums_not_available"));
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("forum");

$tpl->setVariable("COUNT_FORUM", $lng->txt("forums_count").": ".$frmNum);
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("forums_overview"));
$tpl->setVariable("TXT_FORUM", $lng->txt("forum"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
$tpl->setVariable("TXT_NUM_THREADS", $lng->txt("forums_threads"));
$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_articles"));
$tpl->setVariable("TXT_NUM_VISITS", $lng->txt("visits"));
$tpl->setVariable("TXT_LAST_POST", $lng->txt("forums_last_post"));
$tpl->setVariable("TXT_MODS", $lng->txt("forums_moderators"));
$tpl->parseCurrentBlock();

$tpl->setVariable("TPLPATH", $tpl->vars["TPLPATH"]);

$tpl->show();
?>
