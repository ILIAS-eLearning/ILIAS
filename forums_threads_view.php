<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* forums_threads_view
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./Modules/Forum/classes/class.ilObjForum.php";
require_once "./Modules/Forum/classes/class.ilFileDataForum.php";

$lng->loadLanguageModule("forum");

$forumObj = new ilObjForum($_GET["ref_id"]);
$frm =& $forumObj->Forum;

// SAVE LAST ACCESS
$forumObj->updateLastAccess($ilUser->getId(),(int) $_GET['thr_pk']);

// mark post read if explorer link was clicked
if($_GET['thr_pk'] and $_GET['pos_pk'])
{
	$forumObj->markPostRead($ilUser->getId(),(int) $_GET['thr_pk'],(int) $_GET['pos_pk']);
}
$file_obj =& new ilFileDataForum($forumObj->getId(),$_GET["pos_pk"]);

$frm->setForumId($forumObj->getId());
$frm->setForumRefId($forumObj->getRefId());

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_view.html");
//$tpl->setVariable("CONTENT", "kk");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
//$tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
// catch stored message
sendInfo();
// display infopanel if something happened
infoPanel();

if (!$rbacsystem->checkAccess("read,visible", $_GET["ref_id"]))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

// UPLOAD FILE
// DELETE FILE
if(isset($_POST["cmd"]["delete_file"]))
{
	$file_obj->unlinkFiles($_POST["del_file"]);
	sendInfo("File deleted");
}
// DOWNLOAD FILE
if($_GET["file"])
{
	if(!$path = $file_obj->getAbsolutePath(urldecode($_GET["file"])))
	{
		sendInfo("Error reading file!");
	}
	else
	{
		ilUtil::deliverFile($path,urldecode($_GET["file"]));
	}
}

$tpl->setVariable("TXT_FORUM_ARTICLES", $lng->txt("forums_posts"));
$session_name = "viewmode_".$forumObj->getId();
if($_SESSION[$session_name] == 'flat')
{
	$new_order = "answers";
	$orderField = "frm_posts_tree.date";
}
else
{
	$new_order = "date";
	$orderField = "frm_posts_tree.rgt";
}

// sorting val for posts
/*
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
*/
#tpl->setVariable("LINK_SORT", "<b>></b><a href=\"forums_threads_view.php?orderby=".$new_order."&thr_pk=".$_GET["thr_pk"]."&ref_id=".$_GET["ref_id"]."\">".$lng->txt("order_by")." ".$lng->txt($new_order)."</a>");

// get forum- and thread-data
$frm->setWhereCondition("top_frm_fk = ".$frm->getForumId());

if (is_array($topicData = $frm->getOneTopic()))
{
	$frm->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
	$threadData = $frm->getOneThread();

	$tpl->setCurrentBlock("header_image");
	$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_frm_b.gif"));
	$tpl->parseCurrentBlock();
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("forums_thread")." \"".$threadData["thr_subject"]."\"");

	// Visit-Counter
	$frm->setDbTable("frm_threads");
	$frm->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
	$frm->updateVisits($_GET["thr_pk"]);

	// ********************************************************************************
	// build location-links
	include_once("./Modules/Forum/classes/class.ilForumLocatorGUI.php");
	$frm_loc =& new ilForumLocatorGUI();
	$frm_loc->setRefId($_GET["ref_id"]);
	$frm_loc->setForum($frm);
	$frm_loc->setThread($_GET["thr_pk"], $threadData["thr_subject"]);
	$frm_loc->display();
                                                                 
	// set tabs
	// display different buttons depending on viewmode

	$session_name = "viewmode_".$forumObj->getId();
	$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
	
	$ilTabs->setBackTarget($lng->txt("all_topics"),
		"repository.php?ref_id=$_GET[ref_id]",
		$t_frame);

	$ilTabs->addTarget("order_by_answers",
		"forums_frameset.php?viewmode=tree&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]",
		"","", $t_frame);

	$ilTabs->addTarget("order_by_date",
		"forums_frameset.php?viewmode=flat&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]",
		"","", $t_frame);

	if (!isset($_SESSION[$session_name]) or $_SESSION[$session_name] == "flat")
	{
		$ilTabs->setTabActive("order_by_date");
	}
	else
	{
		$ilTabs->setTabActive("order_by_answers");
	}

	$html = $ilTabs->getHTML();
	$tpl->setVariable("TABS", $html);
	
	/*
	$tpl->setCurrentBlock("tab");	
	$tpl->setVariable("TAB_TYPE", $ttabtype);
	$tpl->setVariable("TAB_LINK", "forums_frameset.php?viewmode=tree&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
	$tpl->setVariable("TAB_TEXT", $lng->txt("order_by")." ".$lng->txt("answers"));
	$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
	$tpl->setVariable("TAB_TARGET", $t_frame);
	$tpl->parseCurrentBlock();

	$tpl->setCurrentBlock("tab");
	$tpl->setVariable("TAB_TYPE", $ftabtype);
	$tpl->setVariable("TAB_LINK", "forums_frameset.php?viewmode=flat&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
	$tpl->setVariable("TAB_TEXT", $lng->txt("order_by")." ".$lng->txt("date"));
	$tpl->setVariable("TAB_TARGET", $t_frame);
	$tpl->parseCurrentBlock();*/

	if ($ilias->getSetting("forum_notification") != 0)
	{
		$tpl->setCurrentBlock("tab");
		$tpl->setVariable("TAB_TYPE", "tabinactive");
		$tpl->setVariable("TAB_LINK", "forums_threads_notification.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
		$tpl->setVariable("TAB_TEXT", $lng->txt("forums_notification"));
		$tpl->setVariable("TAB_TARGET", "_self");
		$tpl->parseCurrentBlock();
	}

	// menu template (contains linkbar, new topic and print thread button)
	$menutpl =& new ilTemplate("tpl.forums_threads_menu.html", true, true);

	if($forumObj->getCountUnread($ilUser->getId(),(int) $_GET['thr_pk']))
	{
		$menutpl->setCurrentBlock("btn_cell");
		$menutpl->setVariable("BTN_LINK","forums_frameset.php?mark_read=1&ref_id=".$_GET["ref_id"]."&thr_pk=".$_GET['thr_pk']);
		$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
		$menutpl->setVariable("BTN_TARGET","target=\"$t_frame\"");
		$menutpl->setVariable("BTN_TXT", $lng->txt("forums_mark_read"));
		$menutpl->parseCurrentBlock();
	}

	/*
	if ($rbacsystem->checkAccess("edit_post", $_GET["ref_id"]))
	{
		$menutpl->setCurrentBlock("btn_cell");
		$menutpl->setVariable("BTN_LINK","forums_threads_new.php?ref_id=".$_GET["ref_id"]);
		$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
		$menutpl->setVariable("BTN_TARGET","target=\"$t_frame\"");
		$menutpl->setVariable("BTN_TXT", $lng->txt("forums_new_thread"));
		$menutpl->parseCurrentBlock();
	}*/

	// print thread
	$menutpl->setCurrentBlock("btn_cell");
	$menutpl->setVariable("BTN_LINK","forums_export.php?print_thread=".$_GET["thr_pk"].
		"&thr_top_fk=".$threadData["thr_top_fk"]);
	$menutpl->setVariable("BTN_TARGET","target=\"_new\"");
	$menutpl->setVariable("BTN_TXT", $lng->txt("forums_print_thread"));
	$menutpl->parseCurrentBlock();

	// ********************************************************************************

	// form processing (edit & reply)
	if ($_GET["cmd"] == "ready_showreply" || $_GET["cmd"] == "ready_showedit" || $_GET["cmd"] == "ready_censor")
	{
		$formData = $_POST["formData"];

		if ($_GET["cmd"] != "ready_censor")
		{
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
//echo "<br>1:".htmlentities($formData["message"]);
					$newPost = $frm->generatePost($topicData["top_pk"], $_GET["thr_pk"],
												  $_SESSION["AccountId"], ilUtil::stripSlashes($formData["message"]),
												  $_GET["pos_pk"],$_POST["notify"],$_POST["anonymize"],
												  $_POST["subject"]
												  	? ilUtil::stripSlashes($_POST["subject"])
													: $threadData["thr_subject"]);
					sendInfo($lng->txt("forums_post_new_entry"));
					if(isset($_FILES["userfile"]))
					{
						$tmp_file_obj =& new ilFileDataForum($forumObj->getId(),$newPost);
						$tmp_file_obj->storeUploadedFile($_FILES["userfile"]);
					}

				}
				else
				{
					// edit: update post
					if ($frm->updatePost(ilUtil::stripSlashes($formData["message"]), $_GET["pos_pk"],$_POST["notify"],
										 $_POST["subject"]
										 	? ilUtil::stripSlashes($_POST["subject"])
											: $threadData["thr_subject"]))
					{
						sendInfo($lng->txt("forums_post_modified"));
					}
					if(isset($_FILES["userfile"]))
					{
						$file_obj->storeUploadedFile($_FILES["userfile"]);
					}
				}
			}

		} // if ($_GET["cmd"] != "ready_censor")
		// insert censorship
		elseif ($_POST["confirm"] != "" && $_GET["cmd"] == "ready_censor")
		{
			$frm->postCensorship($formData["cens_message"], $_GET["pos_pk"],1);
		}
		elseif ($_POST["cancel"] != "" && $_GET["cmd"] == "ready_censor")
		{
			$frm->postCensorship($formData["cens_message"], $_GET["pos_pk"]);
		}
	}

	// get first post of thread
	$first_node = $frm->getFirstPostNode($_GET["thr_pk"]);

	// get complete tree of thread
	$frm->setOrderField($orderField);
//echo "orderField:$orderField:<br>";

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
//echo ":$linkbar:";
		if ($linkbar != "")
		{
			$menutpl->setCurrentBlock("linkbar");
			$menutpl->setVariable("LINKBAR", $linkbar);
			$menutpl->parseCurrentBlock();
		}
	}

	$menutpl->setCurrentBlock("btn_row");
	$menutpl->parseCurrentBlock();
	$tpl->setVariable("THREAD_MENU", $menutpl->get());


	// assistance val for anchor-links
	$jump = 0;

	// generate post-dates
	foreach ($subtree_nodes as $node)
	{
//echo ":".$frm->convertDate($node["create_date"]).":<br>";
		if ($_GET["pos_pk"] && $_GET["pos_pk"] == $node["pos_pk"])
		{
			$jump ++;
		}

		if ($posNum > $pageHits && $z >= ($Start+$pageHits))
		{
			// if anchor-link was not found ...
			if ($_GET["pos_pk"] && $jump < 1)
			{
				header("location: forums_threads_view.php?thr_pk=".$_GET["thr_pk"]."&ref_id=".
					   $_GET["ref_id"]."&pos_pk=".$_GET["pos_pk"]."&offset=".($Start+$pageHits)."&orderby=".$_GET["orderby"]);
				exit();
			}
			else
			{
				break;
			}
		}

		if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)
		{
			if ($rbacsystem->checkAccess("edit_post", $_GET["ref_id"]))
			{
				// reply/edit
				if (($_GET["cmd"] == "showreply" || $_GET["cmd"] == "showedit") && $_GET["pos_pk"] == $node["pos_pk"])
				{
					// EDIT ATTACHMENTS
					if (count($file_obj->getFilesOfPost()) && $_GET["cmd"] == "showedit")
					{
						foreach ($file_obj->getFilesOfPost() as $file)
						{
							$tpl->setCurrentBlock("ATTACHMENT_EDIT_ROW");
							$tpl->setVariable("FILENAME",$file["name"]);
							$tpl->setVariable("CHECK_FILE",ilUtil::formCheckbox(0,"del_file[]",$file["name"]));
							$tpl->parseCurrentBlock();
						}

						$tpl->setCurrentBlock("reply_attachment_edit");
						$tpl->setVariable("FILE_DELETE_ACTION",
							"forums_threads_view.php?ref_id=$_GET[ref_id]&cmd=showedit".
							"&pos_pk=$_GET[pos_pk]&thr_pk=$_GET[thr_pk]");
						$tpl->setVariable("TXT_ATTACHMENTS_EDIT",$lng->txt("forums_attachments_edit"));
						$tpl->setVariable("ATTACHMENT_EDIT_DELETE",$lng->txt("forums_delete_file"));
						$tpl->parseCurrentBlock();
					}

					// ADD ATTACHMENTS
					$tpl->setCurrentBlock("reply_attachment");
					$tpl->setVariable("TXT_ATTACHMENTS_ADD",$lng->txt("forums_attachments_add"));
					#						$tpl->setVariable("UPLOAD_ACTION","forums_threads_view.php?ref_id=$_GET[ref_id]&cmd=showedit".
					#										  "&pos_pk=$_GET[pos_pk]&thr_pk=$_GET[thr_pk]");
					$tpl->setVariable("BUTTON_UPLOAD",$lng->txt("upload"));
					$tpl->parseCurrentBlock();
					$tpl->setCurrentBlock("reply_post");
					$tpl->setVariable("REPLY_ANKER", $_GET["pos_pk"]);

					$tpl->setVariable("TXT_FORM_SUBJECT",$lng->txt("forums_subject"));
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
						$tpl->setVariable("SUBJECT_VALUE",ilUtil::prepareFormOutput($threadData["thr_subject"]));
						$tpl->setVariable("FORM_MESSAGE", $frm->prepareText($node["message"],1));
					}
					else
					{
						$tpl->setVariable("SUBJECT_VALUE",ilUtil::prepareFormOutput($node["subject"]));
						$tpl->setVariable("FORM_MESSAGE", $frm->prepareText($node["message"],2));
					}
					// NOTIFY
					include_once 'classes/class.ilMail.php';
					$umail = new ilMail($_SESSION["AccountId"]);

					if ($rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
					{
						global $ilUser;
						
						// only if gen. notification is disabled...
						if(!$frm->isNotificationEnabled($ilUser->getId(), $_GET["thr_pk"]))
						{
							$tpl->setCurrentBlock("notify");
							$tpl->setVariable("NOTIFY",$lng->txt("forum_notify_me"));
							$tpl->setVariable("NOTIFY_CHECKED",$node["notify"] ? "checked=\"checked\"" : "");
							$tpl->parseCurrentBlock();
						}
					}

/*					if ($frm->isAnonymized())
					{
						$tpl->setCurrentBlock("anonymize");
						$tpl->setVariable("TXT_ANONYMIZE",$lng->txt("forum_anonymize"));
						$tpl->setVariable("ANONYMIZE",$lng->txt("forum_anonymize_desc"));
						$tpl->parseCurrentBlock();
					}*/

					$tpl->setVariable("SUBMIT", $lng->txt("submit"));
					$tpl->setVariable("RESET", $lng->txt("reset"));
					$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=ready_".$_GET["cmd"]."&ref_id=".
									  $_GET["ref_id"]."&pos_pk=".$_GET["pos_pk"]."&thr_pk=".$_GET["thr_pk"].
									  "&offset=".$Start."&orderby=".$_GET["orderby"]);
					$tpl->parseCurrentBlock("reply_post");

				} // if (($_GET["cmd"] == "showreply" || $_GET["cmd"] == "showedit") && $_GET["pos_pk"] == $node["pos_pk"])
				else
				{
					// button: delete article
					if ($rbacsystem->checkAccess("delete_post", $_GET["ref_id"]))
					{
						// 2. delete-level
						if ($_GET["cmd"] == "delete" && $_GET["pos_pk"] == $node["pos_pk"])
						{
							$tpl->setCurrentBlock("kill_cell");
							$tpl->setVariable("KILL_ANKER", $_GET["pos_pk"]);
							$tpl->setVariable("KILL_SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">");
							$tpl->setVariable("TXT_KILL", $lng->txt("forums_info_delete_post"));
//							$tpl->setVariable("DEL_FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=ready_delete&ref_id=".$_GET["ref_id"]."&pos_pk=".$node["pos_pk"]."&thr_pk=".$_GET["thr_pk"]."&offset=".$Start."&orderby=".$_GET["orderby"]);
							$tpl->setVariable("DEL_FORMACTION", "forums_frameset.php?cmd=ready_delete&ref_id=".
											  $_GET["ref_id"]."&pos_pk=".$node["pos_pk"]."&thr_pk=".$_GET["thr_pk"].
											  "&offset=".$Start."&orderby=".$_GET["orderby"]);
							$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
							//$t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "frm");
							$tpl->setVariable("DEL_FORM_TARGET", $t_frame);
							$tpl->setVariable("CANCEL_BUTTON", $lng->txt("cancel"));
							$tpl->setVariable("CONFIRM_BUTTON", $lng->txt("confirm"));
							$tpl->parseCurrentBlock("kill_cell");
						}
						else
						{
							// 1. delete-level
							if ($_GET["cmd"] != "censor" || $_GET["pos_pk"] != $node["pos_pk"])
							{
								$tpl->setCurrentBlock("del_cell");
								$tpl->setVariable("DEL_LINK",
									"forums_threads_view.php?cmd=delete&pos_pk=".
									$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start.
									"&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".
									$node["pos_pk"]);
								$tpl->setVariable("DEL_BUTTON", $lng->txt("delete"));
								$tpl->parseCurrentBlock("del_cell");
							}
						}

						// censorship
						// 2. cens formular
						if ($_GET["cmd"] == "censor" && $_GET["pos_pk"] == $node["pos_pk"])
						{
							$tpl->setCurrentBlock("censorship_cell");
							$tpl->setVariable("CENS_ANKER", $_GET["pos_pk"]);
							$tpl->setVariable("CENS_SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">");
							$tpl->setVariable("CENS_FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=ready_censor&ref_id=".
											  $_GET["ref_id"]."&pos_pk=".$node["pos_pk"]."&thr_pk=".$_GET["thr_pk"].
											  "&offset=".$Start."&orderby=".$_GET["orderby"]);
							$tpl->setVariable("TXT_CENS_MESSAGE", $lng->txt("forums_the_post"));
							$tpl->setVariable("TXT_CENS_COMMENT", $lng->txt("forums_censor_comment").":");
							$tpl->setVariable("CENS_MESSAGE", $frm->prepareText($node["pos_cens_com"],2));
							$tpl->setVariable("CANCEL_BUTTON", $lng->txt("cancel"));
							$tpl->setVariable("CONFIRM_BUTTON", $lng->txt("confirm"));

							if ($node["pos_cens"] == 1)
							{
								$tpl->setVariable("TXT_CENS", $lng->txt("forums_info_censor2_post"));
								$tpl->setVariable("CANCEL_BUTTON", $lng->txt("yes"));
								$tpl->setVariable("CONFIRM_BUTTON", $lng->txt("no"));
							}
							else
								$tpl->setVariable("TXT_CENS", $lng->txt("forums_info_censor_post"));

							$tpl->parseCurrentBlock("censorship_cell");
						}
						elseif (($_GET["cmd"] == "delete" && $_GET["pos_pk"] != $node["pos_pk"]) || $_GET["cmd"] != "delete")
						{
							// 1. cens button
							$tpl->setCurrentBlock("cens_cell");
							$tpl->setVariable("CENS_LINK",
								"forums_threads_view.php?cmd=censor&pos_pk=".
								$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".
								$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".
								$node["pos_pk"]);
							$tpl->setVariable("CENS_BUTTON", $lng->txt("censorship"));
							$tpl->parseCurrentBlock("cens_cell");
						}
						// READ LINK
						if(!$forumObj->isRead($ilUser->getId(),$node['pos_pk']))
						{
							$tpl->setCurrentBlock("read_cell");
							$tpl->setVariable("READ_LINK", "forums_threads_view.php?pos_pk=".
											  $node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".
											  $Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".
											  $node["pos_pk"]);
							$tpl->setVariable("READ_BUTTON", $lng->txt("is_read"));
							$tpl->parseCurrentBlock("read_cell");
						}

					} // if ($rbacsystem->checkAccess("delete post", $_GET["ref_id"]))

					if (($_GET["cmd"] != "delete") || ($_GET["cmd"] == "delete" && $_GET["pos_pk"] != $node["pos_pk"]))
					{
						if ($_GET["cmd"] != "censor" || $_GET["pos_pk"] != $node["pos_pk"])
						{
							// button: edit article
							if ($frm->checkEditRight($node["pos_pk"]) && $node["pos_cens"] != 1)
							{
								$tpl->setCurrentBlock("edit_cell");
								$tpl->setVariable("EDIT_LINK","forums_threads_view.php?cmd=showedit&pos_pk=".
												$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start."&orderby=".
												$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]);
								$tpl->setVariable("EDIT_BUTTON", $lng->txt("edit"));
								$tpl->parseCurrentBlock("edit_cell");
							}

							if ($node["pos_cens"] != 1)
							{
								// button: print
								$tpl->setCurrentBlock("print_cell");
								//$tpl->setVariable("SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">");
								$tpl->setVariable("PRINT_LINK", "forums_export.php?&print_post=".
												$node["pos_pk"]."&top_pk=".$topicData["top_pk"]."&thr_pk=".
												$threadData["thr_pk"]);
								$tpl->setVariable("PRINT_BUTTON", $lng->txt("print"));
								$tpl->parseCurrentBlock("print_cell");
							}
							if ($node["pos_cens"] != 1)
							{
							// button: reply
							$tpl->setCurrentBlock("reply_cell");
							$tpl->setVariable("REPLY_LINK",
								"forums_threads_view.php?cmd=showreply&pos_pk=".
								$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start."&orderby=".
								$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]);
							$tpl->setVariable("REPLY_BUTTON", $lng->txt("reply"));
							$tpl->parseCurrentBlock("reply_cell");
							}
						}

						$tpl->setVariable("POST_ANKER", $node["pos_pk"]);

					} // if (($_GET["cmd"] != "delete") || ($_GET["cmd"] == "delete" && $_GET["pos_pk"] != $node["pos_pk"]))

					$tpl->setVariable("SPACER","<hr noshade=\"noshade\" width=\"100%\" size=\"1\" align=\"center\">");

				} // else

			} // if ($rbacsystem->checkAccess("write", $_GET["ref_id"]))
			else
			{
				if(!$forumObj->isRead($ilUser->getId(),$node['pos_pk']))
				{
					$tpl->setCurrentBlock("read_cell");
					$tpl->setVariable("READ_BUTTON","<a href=\"forums_threads_view.php?pos_pk=".
									  $node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".
									  $Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".
									  $node["pos_pk"]."\">".$lng->txt("is_read")."</a>");
					$tpl->parseCurrentBlock("read_cell");
				}

				$tpl->setVariable("POST_ANKER", $node["pos_pk"]);
			}
			// DOWNLOAD ATTACHMENTS
			$tmp_file_obj =& new ilFileDataForum($forumObj->getId(),$node["pos_pk"]);
			if(count($tmp_file_obj->getFilesOfPost()))
			{
				if($node["pos_pk"] != $_GET["pos_pk"] || $_GET["cmd"] != "showedit")
				{
					foreach($tmp_file_obj->getFilesOfPost() as $file)
					{
						$tpl->setCurrentBlock("attachment_download_row");
						$tpl->setVariable("HREF_DOWNLOAD","forums_threads_view.php?ref_id=$_GET[ref_id]&pos_pk=$node[pos_pk]&file=".
										  urlencode($file["name"]));
						$tpl->setVariable("TXT_FILENAME", $file["name"]);
						$tpl->parseCurrentBlock();
					}
					$tpl->setCurrentBlock("attachments");
					$tpl->setVariable("TXT_ATTACHMENTS_DOWNLOAD",$lng->txt("forums_attachments"));
					$tpl->setVariable("DOWNLOAD_IMG",
						ilUtil::getImagePath("icon_attachment.gif"));
					$tpl->setVariable("TXT_DOWNLOAD_ATTACHMENT",
						$lng->txt("forums_download_attachment"));
					$tpl->parseCurrentBlock();
				}
			}

			$tpl->setCurrentBlock("posts_row");
			$rowCol = ilUtil::switchColor($z,"tblrow1","tblrow2");
			if ($_GET["cmd"] != "censor" || $_GET["pos_pk"] != $node["pos_pk"])
			{
				$tpl->setVariable("ROWCOL", $rowCol);
			}
			else
			{
				$tpl->setVariable("ROWCOL", "tblrowmarked");
			}

			// get author data

			unset($author);
			if (ilObject::_exists($node["author"]))
			{
				$author = $frm->getUser($node["author"]);
			}
			else
			{
				unset($node["author"]);
			}
			/*
			$tpl->setVariable("AUTHOR","<a href=\"forums_user_view.php?ref_id=".$_GET["ref_id"]."&user=".
							  $node["author"]."&backurl=forums_threads_view&offset=".$Start."&orderby=".
							  $_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."\">".$author->getLogin()."</a>");
			*/

			// GET USER DATA, USED FOR IMPORTED USERS
			$usr_data = $frm->getUserData($node["author"],$node["import_name"]);

			$backurl = urlencode("forums_threads_view.php?ref_id=".$_GET["ref_id"].
					 "&thr_pk=".$_GET["thr_pk"].
					 "&pos_pk=".$node["pos_pk"]."#".$node["pos_pk"]);

			// get create- and update-dates
			if ($node["update_user"] > 0)
			{
				$span_class = "";

				// last update from moderator?
				$posMod = $frm->getModeratorFromPost($node["pos_pk"]);

				if (is_array($posMod) && $posMod["top_mods"] > 0)
				{
					$MODS = $rbacreview->assignedUsers($posMod["top_mods"]);
					
					if (is_array($MODS))
					{
						if (in_array($node["update_user"], $MODS))
							$span_class = "moderator_small";
					}
				}

				$node["update"] = $frm->convertDate($node["update"]);
				#unset($lastuser);
				#$lastuser = $frm->getUser($node["update_user"]);

				$last_user_data = $frm->getUserData($node['update_user']);
				if ($span_class == "")
					$span_class = "small";


				if($last_user_data['usr_id'])
				{
					$edited_author = "<a href=\"forums_user_view.php?ref_id=".$_GET["ref_id"]."&thr_pk=".$_GET["thr_pk"]."&user=".
						$last_user_data['usr_id']."&backurl=".$backurl."\">".$last_user_data['login']."</a>";
				}
				else
				{
					$edited_author = $last_user_data['login'];
				}

				$tpl->setCurrentBlock("post_update");
				$tpl->setVariable("POST_UPDATE", $lng->txt("edited_at").": ".
					$node["update"]." - ".strtolower($lng->txt("by"))." ".$edited_author);
				$tpl->parseCurrentBlock();

			} // if ($node["update_user"] > 0)
			
			
			if($node["author"])
			{
				$user_obj = new ilObjUser($usr_data["usr_id"]);
				// user image
				$webspace_dir = ilUtil::getWebspaceDir();
				$image_dir = $webspace_dir."/usr_images";
				$xthumb_file = $image_dir."/usr_".$user_obj->getID()."_xsmall.jpg";
				if ($user_obj->getPref("public_upload") == "y" &&
					$user_obj->getPref("public_profile") == "y" &&
					@is_file($xthumb_file))
				{
					$tpl->setCurrentBlock("usr_image");
					$tpl->setVariable("USR_IMAGE", $xthumb_file."?t=".rand(1, 99999));
					$tpl->parseCurrentBlock();
					//$tpl->setCurrentBlock("posts_row");
				}
				$tpl->setCurrentBlock("posts_row");

				//$t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "frm");
				//$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
				$tpl->setVariable("AUTHOR","<a href=\"forums_user_view.php?ref_id=".$_GET["ref_id"]."&thr_pk=".$_GET["thr_pk"]."&user=".
								  $usr_data["usr_id"]."&backurl=".$backurl."\">".$usr_data["login"]."</a>");

				if ($frm->_isModerator($_GET["ref_id"], $ilUser->getId()))
				{
					$tpl->setVariable("USR_NAME", $usr_data["firstname"]." ".$usr_data["lastname"]);
				}
			}
			else
			{
				$tpl->setCurrentBlock("posts_row");
				$tpl->setVariable("AUTHOR",$usr_data["login"]);
			}

			if($node["author"])
			{
				$tpl->setVariable("TXT_REGISTERED", $lng->txt("registered_since").":");
				$tpl->setVariable("REGISTERED_SINCE",$frm->convertDate($author->getCreateDate()));
				$numPosts = $frm->countUserArticles($author->id);
				$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_posts").":");
				$tpl->setVariable("NUM_POSTS",$numPosts);
			}

			// make links in post usable
			$node["message"] = ilUtil::makeClickable($node["message"]);

			// prepare post
			$node["message"] = $frm->prepareText($node["message"]);

			$tpl->setVariable("TXT_CREATE_DATE",$lng->txt("forums_thread_create_date"));

			if($forumObj->isRead($ilUser->getId(),$node['pos_pk']))
			{
				$tpl->setVariable("SUBJECT",$node["subject"]);
			}
			else
			{
				if($forumObj->isNew($ilUser->getId(),$_GET['thr_pk'],$node['pos_pk']))
				{
					$tpl->setVariable("SUBJECT","<i><b>".$node["subject"]."</b></i>");
				}
				else
				{
					$tpl->setVariable("SUBJECT","<b>".$node["subject"]."</b>");
				}
			}

			$tpl->setVariable("POST_DATE",$frm->convertDate($node["create_date"]));
			$tpl->setVariable("SPACER","<hr noshade width=100% size=1 align='center'>");
			if ($node["pos_cens"] > 0)
				$tpl->setVariable("POST","<span class=\"moderator\">".nl2br(stripslashes($node["pos_cens_com"]))."</span>");
			else
			{
				// post from moderator?
				$modAuthor = $frm->getModeratorFromPost($node["pos_pk"]);

				$spanClass = "";

				if (is_array($modAuthor) && $modAuthor["top_mods"] > 0)
				{
					unset($MODS);

					$MODS = $rbacreview->assignedUsers($modAuthor["top_mods"]);

					if (is_array($MODS))
					{
						if (in_array($node["author"], $MODS))
							$spanClass = "moderator";
					}
				}
				if ($spanClass != "")
					$tpl->setVariable("POST","<span class=\"".$spanClass."\">".nl2br($node["message"])."</span>");
				else
					$tpl->setVariable("POST",nl2br($node["message"]));
			}

			$tpl->parseCurrentBlock("posts_row");

		} // if (($posNum > $pageHits && $z >= $Start) || $posNum <= $pageHits)

		$z++;

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

$tpl->setVariable("TPLPATH", $tpl->vars["TPLPATH"]);

$tpl->setCurrentBlock("perma_link");
$tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH.
	"/goto.php?target=".
	"frm".
	"_".$_GET["ref_id"]."_".$_GET["thr_pk"]."&client_id=".CLIENT_ID);
$tpl->setVariable("TXT_PERMA_LINK", $lng->txt("perma_link"));
$tpl->setVariable("PERMA_TARGET", "_top");
$tpl->parseCurrentBlock();


$tpl->show();
?>
