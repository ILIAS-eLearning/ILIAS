<?php
/**
* mail
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "./classes/class.ilMailbox.php";
require_once "./classes/class.ilFormatMail.php";

$lng->loadLanguageModule("mail");

$mbox = new ilMailBox($_SESSION["AccountId"]);
$umail = new ilFormatMail($_SESSION["AccountId"]);

// CREATE OUTPUT
$tpl->addBlockFile("CONTENT", "content", "tpl.mail_options.html");
$tpl->setVariable("TXT_OPTIONS_OF",$lng->txt("mail_options_of"));
infoPanel();

setLocator($_GET["mobj_id"],$_SESSION["AccountId"],"");

// RENAME FOLDER
if(isset($_POST["cmd"]["rename"]))
{
	$tmp_data = $mbox->getFolderData($_GET["mobj_id"]);
	if($tmp_data["title"] != $_POST["folder_name"])
	{
		if($mbox->renameFolder($_GET["mobj_id"],$_POST["folder_name"]))
		{
			sendInfo($lng->txt("mail_folder_name_changed"));
		}
		else
		{
			sendInfo($lng->txt("mail_folder_exists"));
		}
	}
}
// DELETE FOLDER ask for confirmation
if(isset($_POST["cmd"]["delete"]))
{
	sendInfo($lng->txt("mail_sure_delete_folder"));
}

// DELETE FOLDER confirmed
if(isset($_POST["cmd"]["confirm"]))
{
	$new_parent = $mbox->getParentFolderId($_GET["mobj_id"]);
	if($mbox->deleteFolder($_GET["mobj_id"]))
	{
		sendInfo($lng->txt("mail_folder_deleted",true));
		header("location: mail_options.php?mobj_id=".$new_parent);
		exit();
	}
	else
	{
		sendInfo($lng->txt("mail_error_delete"));
	}
}
// DELETEING CANCELED
if(isset($_POST["cmd"]["cancel"]))
{
	header("location: mail_options.php?mobj_id=".$_GET["mobj_id"]);
	exit();
}

// SAVE OPTIONS
if(isset($_POST["cmd"]["save"]))
{
	$umail->updateOptions($_POST["signature"],$_POST["linebreak"]);
}
	

// GET FOLDER DATA
$folder_data = $mbox->getFolderData($_GET["mobj_id"]);

include "./include/inc.mail_buttons.php";

$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));

$tpl->setCurrentBlock("content");


// CONFIRM DELETE
if(isset($_POST["cmd"]["delete"]))
{
	$tpl->setCurrentBlock("confirm");
	$tpl->setVariable("TXT_DELETE_CONFIRM",$lng->txt("confirm"));
	$tpl->setVariable("TXT_DELETE_CANCEL",$lng->txt("cancel"));
	$tpl->parseCurrentBlock();
}

// FORM EDIT FOLDER
if($folder_data["type"] == 'user_folder' and !isset($_POST["cmd"]["delete"]))
{
	$tpl->setCurrentBlock('edit');
	$tpl->setVariable("FOLDER_OPTIONS",$lng->txt("mail_folder_options"));
	$tpl->setVariable("TXT_DELETE",$lng->txt("delete"));
	$tpl->setVariable("ACTION","mail_options.php?mobj_id=".$_GET["mobj_id"]);
	$tpl->setVariable("TXT_NAME",$lng->txt("mail_folder_name"));
	$tpl->setVariable("FOLDER_NAME",$folder_data["title"]);
	$tpl->setVariable("TXT_RENAME",$lng->txt("rename"));
	$tpl->parseCurrentBlock();
}

// FORM ADD FOLDER
if(($folder_data["type"] == 'user_folder' or $folder_data["type"] == 'local') 
	and !isset($_POST["cmd"]["delete"]))
{
	$tpl->setCurrentBlock('add');
	$tpl->setVariable("ACTION_ADD","mail_frameset.php?mobj_id=$_GET[mobj_id]");
	$tpl->setVariable("TXT_NAME_ADD",$lng->txt("mail_folder_name"));
	$tpl->setVariable("TXT_FOLDER_ADD",$lng->txt("add"));
	$tpl->parseCurrentBlock();
}

// FORM GLOBAL OPTIONS
if(!isset($_POST["cmd"]["delete"]))
{
	$tpl->setCurrentBlock("options");

	// BEGIN LINEBREAK_OPTIONS
	$tpl->setCurrentBlock("option_line");
	$linebreak = $umail->getLinebreak();
	
	for($i = 50; $i <= 80;$i++)
	{
		$tpl->setVariable("OPTION_VALUE",$i);
		$tpl->setVariable("OPTION_NAME",$i);
		if( $i == $linebreak)
		{
			$tpl->setVariable("OPTION_SELECTED","selected");
		}
		$tpl->parseCurrentBlock();
	}
	$tpl->setVariable("GLOBAL_OPTIONS",$lng->txt("mail_global_options"));
	$tpl->setVariable("TXT_LINEBREAK", $lng->txt("linebreak"));
	$tpl->setVariable("TXT_SIGNATURE", $lng->txt("signature"));
	$tpl->setVariable("CONTENT",$umail->getSignature());
	$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	$tpl->parseCurrentBlock();
}
$tpl->show();
?>