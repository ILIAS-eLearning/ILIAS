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
* mail search recipients,groups
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "classes/class.ilFormatMail.php";
require_once "classes/class.ilFileDataMail.php";

$lng->loadLanguageModule("mail");
$lng->loadLanguageModule("forum");

$mfile = new ilFileDataMail($_SESSION["AccountId"]);

// GET SAVED DATA
$umail = new ilFormatMail($_SESSION["AccountId"]);
$mail_data = $umail->getSavedData();

$_POST["filename"] = $_POST["filename"] ? $_POST["filename"] : array();

$tpl->addBlockFile("CONTENT", "content", "tpl.mail_attachment.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->setVariable("TXT_ATTACHMENT",$lng->txt("attachment"));
infoPanel();

// LOCATOR
setLocator($_GET["mobj_id"],$_SESSION["AccountId"],"");

if(isset($_POST["attachment"]["adopt"]))
{
	$umail->saveAttachments($_POST["filename"]);
	header("location:mail_new.php?mobj_id=$_GET[mobj_id]&type=attach");
	exit();

}
if(isset($_POST["attachment"]["cancel"]))
{
	header("location:mail_new.php?mobj_id=$_GET[mobj_id]&type=attach");
	exit;

}
if(isset($_POST["attachment"]["delete"]))
{
	if(!$_POST["filename"])
	{
		sendInfo($lng->txt("mail_select_one_file"));
		$error_delete = true;
	}
	else
	{
		sendInfo($lng->txt("mail_sure_delete_file"));
	}
}
// UPLOAD FILE
if(isset($_POST["cmd"]["upload"]))
{
	if($mfile->storeUploadedFile($_FILES['userfile']) == 1)
	{
		sendInfo($lng->txt("mail_maxsize_attachment_error")." ".$mfile->getUploadLimit()." K".$lng->txt("mail_byte"));
	}
}
// CONFIRM CANCELED
if(isset($_POST["cmd"]["cancel"]))
{
	header("location:mail_attachment.php?mobj_id=$_GET[mobj_id]");
	exit;
}
// DELETE CONFIRMED
if(isset($_POST["cmd"]["confirm"]))
{
	if(!$_POST["filename"])
	{
		sendInfo($lng->txt("mail_select_one_mail"));
	}
	else if($error = $mfile->unlinkFiles($_POST["filename"]))
	{
		sendInfo($lng->txt("mail_error_delete_file")." ".$error);
	}
	else
	{
		sendInfo($lng->txt("mail_files_deleted"));
	}
}

// BUTTONS
include "./include/inc.mail_buttons.php";

$tpl->setVariable("ACTION","mail_attachment.php?mobj_id=$_GET[mobj_id]");
$tpl->setVariable("UPLOAD","mail_attachment.php?mobj_id=$_GET[mobj_id]");

// BEGIN CONFIRM_DELETE
if(isset($_POST["attachment"]["delete"]) and !$error_delete and !isset($_POST["cmd"]["confirm"]))
{
	$tpl->setCurrentBlock("confirm_delete");
	$tpl->setVariable("BUTTON_CONFIRM",$lng->txt("confirm"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

// SET STATIC VARIABLES ;-)
$tpl->setVariable("TXT_ATTACHMENT",$lng->txt("attachment"));
$tpl->setVariable("TXT_FILENAME",$lng->txt("mail_file_name"));
$tpl->setVariable("TXT_FILESIZE",$lng->txt("mail_file_size"));
$tpl->setVariable("TXT_CREATE_TIME",$lng->txt("forums_thread_create_date"));
$tpl->setVariable("TXT_NEW_FILE",$lng->txt("mail_new_file"));

// ACTIONS
$tpl->setVariable("BUTTON_ATTACHMENT_ADOPT",$lng->txt("adopt"));
$tpl->setVariable("BUTTON_ATTACHMENT_CANCEL",$lng->txt("cancel"));
$tpl->setVariable("BUTTON_ATTACHMENT_DELETE",$lng->txt("delete"));

// BUTTONS
$tpl->setVariable("BUTTON_SUBMIT",$lng->txt("submit"));
$tpl->setVariable("BUTTON_UPLOAD",$lng->txt("upload"));

// BEGIN FILES
if($files = $mfile->getUserFilesData())
{
	$counter = 0;
	foreach($files as $file)
	{
		$tpl->setCurrentBlock('files');
		if(in_array($file["name"],$mail_data["attachments"]) ||
		   in_array($file["name"],$_POST["filename"]))
		{
			$tpl->setVariable("CHECKED",'checked');
		}
		$tpl->setVariable('CSSROW',++$counter%2 ? 'tblrow1' : 'tblrow2');
		$tpl->setVariable('FILE_NAME',$file["name"]);
		$tpl->setVariable("NAME",$file["name"]);
		$tpl->setVariable("SIZE",$file["size"]);
		$tpl->setVariable("CTIME",$file["ctime"]);
		$tpl->parseCurrentBlock();
	}
}
else
{
	$tpl->setCurrentBlock("no_content");
	$tpl->setVariable("TXT_ATTACHMENT_NO",$lng->txt("mail_no_attachments_found"));
	$tpl->parseCurrentBlock();
}
$tpl->show();
?>
