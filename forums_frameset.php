<?php
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

// NECESSARY CAUSE MUST EVENTUAL RELOADED
// delete post and its sub-posts
if ($_GET["cmd"] == "ready_delete" && $_POST["confirm"] != "")
{
	$forumObj = new ilObjForum($_GET["ref_id"]);
	$frm = new ilForum();

	$frm->setForumId($forumObj->getId());

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

$startfilename = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.forums_frameset.html"; 

if (isset($_GET["viewmode"]))
{
	$_SESSION["viewmode"] = $_GET["viewmode"];
}
if (file_exists($startfilename) and ($_SESSION["viewmode"] == "tree"))
{
	$tpl = new ilTemplate("tpl.forums_frameset.html", false, false);
	if(isset($_GET["target"]))
	{
		$tpl->setVariable("FRAME_LEFT_SRC","forums_menu.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
		$tpl->setVariable("FRAME_RIGHT_SRC","forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]".
						  "&pos_pk=$_GET[pos_pk]#$_GET[pos_pk]");
	}
	else
	{
		$tpl->setVariable("FRAME_LEFT_SRC","forums_menu.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
		$tpl->setVariable("FRAME_RIGHT_SRC","forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
	}
	$tpl->show();
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
