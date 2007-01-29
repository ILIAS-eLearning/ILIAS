<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
require_once "./classes/class.ilMailbox.php";

$lng->loadLanguageModule("mail");

// ADD FOLDER cmd comes from mail_options button
if(isset($_POST["cmd"]["add"]))
{
	$mbox = new ilMailbox($_SESSION["AccountId"]);

	if(empty($_POST['folder_name_add']))
	{
		ilUtil::sendInfo($lng->txt("mail_insert_folder_name"),true);
		$_GET["target"] = urlencode("mail_options.php?mobj_id=$_GET[mobj_id]");
	}
	else if($new_id = $mbox->addFolder($_GET["mobj_id"],$_POST["folder_name_add"]))
	{
		ilUtil::sendInfo($lng->txt("mail_folder_created"),true);
		$_GET["mobj_id"] = $new_id;
	}
	else
	{
		ilUtil::sendInfo($lng->txt("mail_folder_exists"),true);
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
		ilUtil::sendInfo($lng->txt("mail_folder_deleted"),true);
		$_GET["target"] = urlencode("mail_options.php?mobj_id=".$new_parent);
	}
	else
	{
		ilUtil::sendInfo($lng->txt("mail_error_delete"),true);
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
if ($_SESSION["viewmode"] == "tree")
{
	include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
	$fs_gui = new ilFramesetGUI();
	$fs_gui->setFramesetTitle($lng->txt("mail"));
	$fs_gui->setMainFrameName("content");
	$fs_gui->setSideFrameName("tree");

	$fs_gui->setSideFrameSource("mail_menu.php?expand=1");

	if(isset($_GET["target"]))
	{
		$fs_gui->setMainFrameSource(urldecode($_GET["target"]));
	}
	else
	{
		$fs_gui->setMainFrameSource("mail.php?mobj_id=$_GET[mobj_id]");
	}
	$fs_gui->show();
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
