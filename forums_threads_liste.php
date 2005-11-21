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


// MOVED TO class.ilObjForumGUI.php
// ****************************************************************************
#ilUtil::redirect('repository.php?ref_id='.$_GET['ref_id']);
// ****************************************************************************

function prepOutput($frm)
{
	global $tpl, $lng;

	$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
	$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
	sendInfo();
	require_once("classes/class.ilForumTabsGUI.php");
	$frm_tab =& new ilForumTabsGUI();
	$frm_tab->setRefId($_GET["ref_id"]);
	$frm_tab->setForum($frm);
	$frm_tab->setTabs();

	require_once("classes/class.ilForumLocatorGUI.php");
	$frm_loc =& new ilForumLocatorGUI();
	$frm_loc->setRefId($_GET["ref_id"]);
	$frm_loc->setForum($frm);
	$frm_loc->display();

}

$lng->loadLanguageModule("forum");

$forumObj = new ilObjForum($_GET["ref_id"]);
$frm =& $forumObj->Forum;

$frm->setForumId($forumObj->getId());
$frm->setForumRefId($forumObj->getRefId());

$frm->setWhereCondition("top_frm_fk = ".$forumObj->getId());
$topicData = $frm->getOneTopic();

// REDIRECT TO forums_threads_new.php if there is no topic 
if (!$topicData["top_num_threads"])
{
	ilUtil::redirect("forums_threads_new.php?ref_id=".$forumObj->getRefId());
}

$ilCtrl->getCallStructure(strtolower("ilObjForumGUI"));

if (!$rbacsystem->checkAccess("read,visible", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

// should go to a gui class
if ($_GET["cmd"] != "")
{
	$cmd = $_GET["cmd"];
}
else
{
	if (is_array($_POST["cmd"]))
	{
		$cmd = key($_POST["cmd"]);
	}
}

switch ($cmd)
{
	case "properties":
		prepOutput($frm);
		$tpl->setVariable("HEADER", $lng->txt("frm")." \"".$forumObj->getTitle()."\"");

		//$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		include_once("classes/class.ilObjForumGUI.php");
		$forum_gui = new ilObjForumGUI("", $_GET["ref_id"], true, false);
		$forum_gui->properties();
		$tpl->show();
		exit;
		break;

	case "saveProperties":
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("classes/class.ilObjForumGUI.php");
		$forum_gui = new ilObjForumGUI("", $_GET["ref_id"], true, false);
		$forum_gui->saveProperties();
		break;

	case "permissions":
		prepOutput($frm);
		$tpl->setVariable("HEADER", $lng->txt("frm")." \"".$forumObj->getTitle()."\"");
		include_once("classes/class.ilObjForumGUI.php");
		$forum_gui = new ilObjForumGUI("", $_GET["ref_id"], true, false);
		$forum_gui->setFormAction("permSave","forums_threads_liste.php?ref_id=".$_GET["ref_id"].
			"&cmd=permSave");
		$forum_gui->permObject();
		$tpl->show();
		exit;
		break;

	case "permSave":
		include_once("classes/class.ilObjForumGUI.php");
		$forum_gui = new ilObjForumGUI("", $_GET["ref_id"], true, false);
		$forum_gui->setReturnLocation("permSave","forums_threads_liste.php?ref_id=".$_GET["ref_id"]);
		$forum_gui->permSaveObject();
		exit;
		break;

	case "addRole":
		include_once("classes/class.ilObjForumGUI.php");
		$forum_gui = new ilObjForumGUI("", $_GET["ref_id"], true, false);
		$forum_gui->setReturnLocation("addRole","forums_threads_liste.php?ref_id=".$_GET["ref_id"]."&cmd=permissions");
		$forum_gui->addRoleObject();
		exit;
		break;

}

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("frm")." \"".$forumObj->getTitle()."\"");
$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_liste.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
// display infopanel if something happened
infoPanel();

// start: form operations
if (isset($_POST["cmd"]["submit"]))
{	
	if(is_array($_POST["forum_id"]))
	{
		if ($_POST["action"] == "enable_notifications")
		{
			for ($i = 0; $i < count($_POST["forum_id"]); $i++)
			{
				$frm->enableNotification($ilUser->getId(), $_POST["forum_id"][$i]);
			}

			ilUtil::redirect("repository.php?cmd=showThreads&ref_id=".$_GET["ref_id"]);
		}
		else if ($_POST["action"] == "disable_notifications")
		{
			for ($i = 0; $i < count($_POST["forum_id"]); $i++)
			{
				$frm->disableNotification($ilUser->getId(), $_POST["forum_id"][$i]);
			}

			ilUtil::redirect("repository.php?cmd=showThreads&ref_id=".$_GET["ref_id"]);
		}
		else
		{
			$startTbl = "frm_threads";
		
			require_once "forums_export.php";		
		
			unset($topicData);
		}
	}

}
// end: form operations

// ********************************************************************************
// build location-links

require_once("classes/class.ilForumLocatorGUI.php");
$frm_loc =& new ilForumLocatorGUI();
$frm_loc->setRefId($_GET["ref_id"]);
$frm_loc->setForum($frm);
$frm_loc->display();

require_once("classes/class.ilForumTabsGUI.php");
$frm_tab =& new ilForumTabsGUI();
$frm_tab->setRefId($_GET["ref_id"]);
$frm_tab->setForum($frm);
$frm_tab->setTabs();

$frm->setWhereCondition("top_frm_fk = ".$frm->getForumId());

if (is_array($topicData = $frm->getOneTopic()))
{
	if ($rbacsystem->checkAccess("edit_post", $_GET["ref_id"]))
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
				// GET USER DATA, USED FOR IMPORTED USERS
				$usr_data = $frm->getUserData($thrData["thr_usr_id"],$thrData["import_name"]);


				$tpl->setCurrentBlock("threads_row");
				$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
				$tpl->setVariable("ROWCOL", $rowCol);
				
				$thrData["thr_date"] = $frm->convertDate($thrData["thr_date"]);
				$tpl->setVariable("DATE",$thrData["thr_date"]);
				$tpl->setVariable("TITLE","<a href=\"forums_frameset.php?thr_pk=".
								  $thrData["thr_pk"]."&ref_id=".$_GET["ref_id"]."\">".
								  $thrData["thr_subject"]."</a>");
				
				$tpl->setVariable("NUM_POSTS",$thrData["thr_num_posts"]);	
				
				$tpl->setVariable("NUM_VISITS",$thrData["visits"]);	
				
				// get author data
				/*
				unset($author);
				$author = $frm->getUser($thrData["thr_usr_id"]);	
				$tpl->setVariable("AUTHOR","<a href=\"forums_user_view.php?ref_id=".$_GET["ref_id"]."&user=".
								  $thrData["thr_usr_id"]."&backurl=forums_threads_liste&offset=".
								  $Start."\">".$author->getLogin()."</a>"); 
				*/
				if($thrData["thr_usr_id"] && $usr_data["usr_id"] != 0)
				{
					$tpl->setVariable("AUTHOR","<a href=\"forums_user_view.php?ref_id=".$_GET["ref_id"]."&user=".
									  $usr_data["usr_id"]."&backurl=forums_threads_liste&offset=".
									  $Start."\">".$usr_data["login"]."</a>");
				}
				else
				{
					$tpl->setVariable("AUTHOR",$usr_data["login"]);
				}
					
				
				// get last-post data
				$lpCont = "";				
				if ($thrData["thr_last_post"] != "")
				{
					$lastPost = $frm->getLastPost($thrData["thr_last_post"]);
				}
				// TODOOOOOOOOOOOOOOOOOOO
				$last_usr_data = $frm->getUserData($lastPost["pos_usr_id"],$lastPost["import_name"]);
				if (is_array($lastPost))
				{				
					$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
					$lpCont = $lastPost["pos_date"]."<br/>".strtolower($lng->txt("from"))."&nbsp;";
					$lpCont .= "<a href=\"forums_frameset.php?pos_pk=".
						$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&ref_id=".
						$_GET["ref_id"]."#".$lastPost["pos_pk"]."\">".$last_usr_data["login"]."</a>";
				}

				$tpl->setVariable("LAST_POST", $lpCont);	
				
				$tpl->setVariable("FORUM_ID", $thrData["thr_pk"]);		
				$tpl->setVariable("THR_TOP_FK", $thrData["thr_top_fk"]);		
				
				$tpl->setVariable("TXT_PRINT", $lng->txt("print"));
				
				$tpl->setVariable("THR_IMGPATH",$tpl->tplPath);
				
				if ($frm->isNotificationEnabled($ilUser->getId(), $thrData["thr_pk"]))
				{
					$tpl->setVariable("NOTIFICATION_ENABLED", $lng->txt("forums_notification_enabled"));
				}

				$tpl->parseCurrentBlock("threads_row");
				
			} // if (($thrNum > $pageHits && $z >= $Start) || $thrNum <= $pageHits)
			
			$z ++;
			
		} // while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		
		$tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));		
		$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?ref_id=".$_GET["ref_id"]);
		$tpl->setVariable("TXT_OK",$lng->txt("ok"));			
		$tpl->setVariable("TXT_EXPORT_HTML", $lng->txt("export_html"));
		$tpl->setVariable("TXT_EXPORT_XML", $lng->txt("export_xml"));
		if ($ilias->getSetting("forum_notification") != 0)
		{
			$tpl->setVariable("TXT_DISABLE_NOTIFICATION", $lng->txt("forums_disable_notification"));
			$tpl->setVariable("TXT_ENABLE_NOTIFICATION", $lng->txt("forums_enable_notification"));
		}
		$tpl->setVariable("IMGPATH",$tpl->tplPath);
		
	} // if ($thrNum > 0)	
		
} // if (is_array($topicData = $frm->getOneTopic()))
else
{
	$tpl->setCurrentBlock("threads_no");
	$tpl->setVariable("TXT_MSG_NO_THREADS_AVAILABLE",$lng->txt("forums_threads_not_available"));
	$tpl->parseCurrentBlock("threads_no");
}

$tpl->setCurrentBlock("threadtable");
$tpl->setVariable("COUNT_THREAD", $lng->txt("forums_count_thr").": ".$thrNum);
$tpl->setVariable("TXT_DATE", $lng->txt("date"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_TOPIC", $lng->txt("forums_thread"));
$tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
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
