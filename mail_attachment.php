<?php
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

$mfile = new ilFileDataMail($_SESSION["AccountId"]);

// GET SAVED DATA
$umail = new ilFormatMail($_SESSION["AccountId"]);
$mail_data = $umail->getSavedData();

$_POST["filename"] = $_POST["filename"] ? $_POST["filename"] : array();

$tpl->addBlockFile("CONTENT", "content", "tpl.mail_attachment.html");
$tpl->setVariable("TXT_ATTACHMENT",$lng->txt("attachment"));
infoPanel();

// LOCATOR
setLocator($_GET["mobj_id"],$_SESSION["AccountId"],"");

if(isset($_POST["cmd"]))
{
	switch($_POST["cmd"])
	{
		case "adopt":
			$umail->saveAttachments($_POST["filename"]);
			header("location:mail_new.php?mobj_id=$_GET[mobj_id]&type=attach");
			exit();

		case $lng->txt("upload"):
			$mfile->storeUploadedFile($HTTP_POST_FILES['userfile']);
			break;

		case "cancel":
			header("location:mail_new.php?mobj_id=$_GET[mobj_id]&type=attach");
			exit();

		case "delete":
			if(isset($_POST["confirm"]))
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
				break;
			}
			else if(!isset($_POST["cancel"]))
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
			else if(isset($_POST["cancel"]))
			{
				header("location: mail_attachment.php?mobj_id=$_GET[mobj_id]");
				exit;
			}
			break;

	}
}

// BUTTONS
include "./include/inc.mail_buttons.php";

$tpl->setVariable("ACTION","mail_attachment.php?mobj_id=$_GET[mobj_id]");
$tpl->setVariable("UPLOAD","mail_attachment.php?mobj_id=$_GET[mobj_id]");

// BEGIN CONFIRM_DELETE
if($_POST["cmd"] == "delete" and !$error_delete and !isset($_POST["confirm"]))
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

// ACTIONS
$tpl->setCurrentBlock("actions");
$tpl->setVariable("ACTION_NAME","adopt");
$tpl->setVariable("ACTION_VALUE",$lng->txt("adopt"));
$tpl->parseCurrentBlock();

$tpl->setVariable("ACTION_NAME","delete");
$tpl->setVariable("ACTION_VALUE",$lng->txt("delete"));
$tpl->setVariable("ACTION_SELECTED",$_POST["cmd"] == 'delete' ? 'selected' : '');
$tpl->parseCurrentBlock();

$tpl->setVariable("ACTION_NAME","cancel");
$tpl->setVariable("ACTION_VALUE",$lng->txt("cancel"));
$tpl->parseCurrentBlock();

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