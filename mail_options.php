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

// catch hack attempts
if (!$rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
}

// CREATE OUTPUT
$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_options.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->setCurrentBlock("header_image");
$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mail_b.gif"));
$tpl->parseCurrentBlock();

$tpl->setVariable("HEADER",$lng->txt("mail"));
ilUtil::infoPanel();

setLocator($_GET["mobj_id"],'mail_options.php',$_SESSION["AccountId"],"");

// RENAME FOLDER
if(isset($_POST["cmd"]["rename"]))
{
	$tmp_data = $mbox->getFolderData($_GET["mobj_id"]);
	if($tmp_data["title"] != $_POST["folder_name"])
	{
		if($mbox->renameFolder($_GET["mobj_id"],$_POST["folder_name"]))
		{
			ilUtil::sendInfo($lng->txt("mail_folder_name_changed"));
		}
		else
		{
			ilUtil::sendInfo($lng->txt("mail_folder_exists"));
		}
	}
}
// DELETE FOLDER ask for confirmation
if(isset($_POST["cmd"]["delete"]))
{
	ilUtil::sendInfo($lng->txt("mail_sure_delete_folder"));
}


// SAVE OPTIONS
if(isset($_POST["cmd"]["save"]))
{
	$umail->mail_options->updateOptions($_POST["signature"],(int) $_POST["linebreak"],(int) $_POST["incoming_type"]);
	ilUtil::sendInfo($lng->txt("mail_options_saved"),true);
	header("location: mail.php?mobj_id=$_GET[mobj_id]");
	exit;
}
	

// GET FOLDER DATA
$folder_data = $mbox->getFolderData($_GET["mobj_id"]);

include "./include/inc.mail_buttons.php";

$tpl->setVariable("TXT_MAIL", $lng->txt("mail"));

$tpl->setCurrentBlock("content");

// CONFIRM DELETE
if(isset($_POST["cmd"]["delete"]))
{
	$tpl->setCurrentBlock("confirm_delete");
	$tpl->setVariable("ACTION_DELETE","mail_frameset.php?mobj_id=$_GET[mobj_id]");
	$tpl->setVariable("TXT_DELETE_CONFIRM",$lng->txt("confirm"));
	$tpl->setVariable("TXT_DELETE_CANCEL",$lng->txt("cancel"));
	$tpl->setVariable("FRAME_DELETE",
		ilFrameTargetInfo::_getFrame("MainContent"));

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
	$tpl->setVariable("FRAME_ADD",
		ilFrameTargetInfo::_getFrame("MainContent"));
	$tpl->parseCurrentBlock();
}

// FORM GLOBAL OPTIONS
if(!isset($_POST["cmd"]["delete"]))
{
	$tpl->setCurrentBlock("options");

	// BEGIN INCOMING
	$tpl->setCurrentBlock("option_inc_line");

	$inc = array($lng->txt("mail_incoming_local"),$lng->txt("mail_incoming_smtp"),$lng->txt("mail_incoming_both"));
	foreach($inc as $key => $option)
	{
		$tpl->setVariable("OPTION_INC_VALUE",$key);
		$tpl->setVariable("OPTION_INC_NAME",$option);
		$tpl->setVariable("OPTION_INC_SELECTED",$umail->mail_options->getIncomingType() == $key ? "selected=\"selected\"" : "");
		$tpl->parseCurrentBlock();
	}

	// BEGIN LINEBREAK_OPTIONS
	$tpl->setCurrentBlock("option_line");
	$linebreak = $umail->mail_options->getLinebreak();
	
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
	if(!strlen(ilObjUser::_lookupEmail($ilUser->getId())))
	{
		$tpl->setVariable('INC_DISABLED','disabled="disabled"');
	}
	
	$tpl->setVariable("GLOBAL_OPTIONS",$lng->txt("mail_global_options"));
	$tpl->setVariable("TXT_INCOMING", $lng->txt("mail_incoming"));
	$tpl->setVariable("TXT_LINEBREAK", $lng->txt("linebreak"));
	$tpl->setVariable("TXT_SIGNATURE", $lng->txt("signature"));
	$tpl->setVariable("CONTENT",$umail->mail_options->getSignature());
	$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	$tpl->parseCurrentBlock();
}
$tpl->show();
?>