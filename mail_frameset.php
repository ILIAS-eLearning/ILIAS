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
require_once "./classes/class.ilMailbox.php";

$lng->loadLanguageModule("mail");

$startfilename = $ilias->tplPath.$ilias->account->getPref("skin")."/tpl.mail_frameset.html"; 

// ADD FOLDER cmd comes from mail_options button
if(isset($_POST["cmd"]["add"]))
{
	$mbox = new ilMailbox($_SESSION["AccountId"]);

	if(empty($_POST['folder_name_add']))
	{
		sendInfo($lng->txt("mail_insert_folder_name"),true);
		$_GET["target"] = urlencode("mail_options.php?mobj_id=$_GET[mobj_id]");
	}
	else if($new_id = $mbox->addFolder($_GET["mobj_id"],$_POST["folder_name_add"]))
	{
		sendInfo($lng->txt("mail_folder_created"),true);
		$_GET["mobj_id"] = $new_id;
	}
	else
	{
		sendInfo($lng->txt("mail_folder_exists"),true);
		$_GET["target"] = urlencode("mail_options.php?mobj_id=$_GET[mobj_id]");
	}
}
// DELETE FOLDER confirmed
if(isset($_POST["cmd"]["confirm"]))
{
	$mbox = new ilMailbox($_SESSION["AccountId"]);
	$new_parent = $mbox->getParentFolderId($_GET["mobj_id"]);

	if($mbox->deleteFolder($_GET["mobj_id"]))
	{
		sendInfo($lng->txt("mail_folder_deleted"),true);
		$_GET["target"] = urlencode("mail_options.php?mobj_id=".$new_parent);
	}
	else
	{
		sendInfo($lng->txt("mail_error_delete"),true);
		$_GET["target"] = urlencode("mail_options.php?mobj_id=".$_GET["mobj_id"]);

	}
}
// DELETEING CANCELED
if(isset($_POST["cmd"]["cancel"]))
{
	$_GET["target"] = urlencode("mail_options.php?mobj_id=".$_GET["mobj_id"]);
}

if (isset($_GET["viewmode"]))
{
	$_SESSION["viewmode"] = $_GET["viewmode"];
}
if (file_exists($startfilename) and ($_SESSION["viewmode"] == "tree"))
{
	$tpl = new ilTemplate("tpl.mail_frameset.html", false, false);
	if(isset($_GET["target"]))
	{
		$tpl->setVariable("FRAME_RIGHT_SRC",urldecode($_GET["target"]));
	}
	else
	{
		$tpl->setVariable("FRAME_RIGHT_SRC","mail.php?mobj_id=$_GET[mobj_id]");
	}
	$tpl->show();
}
else
{
	if(isset($_GET["target"]))
	{
		header("location: ".urldecode($_GET["target"]));
	}
	else
	{
		header("location: mail.php?mobj_id=$_GET[mobj_id]");
	}
}
?>
