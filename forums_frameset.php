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
 * mail mainpage
 * 
 * this file shows two frames (mail_menu.php, mail.php)
 * 
 * @author Stefan Meyer <smeyer@databay.de>
 * @package ilias-core
 * @version $Id$
*/
require_once "./include/inc.header.php";
require_once "./classes/class.ilForum.php";
require_once "./classes/class.ilObjForum.php";

$lng->loadLanguageModule("forum");

$forumObj = new ilObjForum($_GET["ref_id"]);

if($_GET['mark_read'])
{
	$forumObj->markThreadRead($ilUser->getId(),(int) $_GET['thr_pk']);
	sendInfo($lng->txt('forums_thread_marked'),true);
}


// delete post and its sub-posts
if ($_GET["cmd"] == "ready_delete" && $_POST["confirm"] != "")
{
	$frm = new ilForum();

	$frm->setForumId($forumObj->getId());
	$frm->setForumRefId($forumObj->getRefId());

	$dead_thr = $frm->deletePost($_GET["pos_pk"]);
		
	// if complete thread was deleted ...
	if ($dead_thr == $_GET["thr_pk"])
	{
		$frm->setWhereCondition("top_frm_fk = ".$forumObj->getId());
		$topicData = $frm->getOneTopic();

		if ($topicData["top_num_threads"] > 0)
		{
			$script = "repository.php";
		}
		else
		{
			$script = "forums_threads_new.php";
		}

		sendInfo($lng->txt("forums_post_deleted"),true);

		header("location: ".$script."?ref_id=".$_GET["ref_id"]);
		exit();
	}
	sendInfo($lng->txt("forums_post_deleted"));
}


$session_name = "viewmode_".$forumObj->getId();

if (isset($_GET["viewmode"]))
{
	$_SESSION[$session_name] = $_GET["viewmode"];
}
if(!$_SESSION[$session_name])
{
	$_SESSION[$session_name] = $forumObj->getDefaultView() == 1 ? 'tree' : 'flat';
}

if ($_SESSION[$session_name] == "tree")
{
	include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
	$fs_gui = new ilFramesetGUI();
	$fs_gui->setMainFrameName("content");
	$fs_gui->setSideFrameName("tree");
	$fs_gui->setFramesetTitle($forumObj->getTitle());

	$tpl = new ilTemplate("tpl.forums_frameset.html", false, false);
	if(isset($_GET["target"]))
	{
		$fs_gui->setSideFrameSource(
			"./forums_menu.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
		$fs_gui->setMainFrameSource(
			"./forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]".
						  "&pos_pk=$_GET[pos_pk]#$_GET[pos_pk]");
	}
	else
	{
		$fs_gui->setSideFrameSource(
			"./forums_menu.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
		$fs_gui->setMainFrameSource(
			"./forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
	}
	$fs_gui->show();
}
else
{
	if(isset($_GET["target"]))
	{
		header("location: forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]".
			   "&pos_pk=$_GET[pos_pk]#$_GET[pos_pk]");
		exit;
	}
	else
	{
		header("location: forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
		exit;
	}
}
?>
