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
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
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


// SAVE OPTIONS
if(isset($_POST["cmd"]["save"]))
{
	$umail->updateOptions($_POST["signature"],$_POST["linebreak"]);
	sendInfo("mail_options_saved",true);
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
	$tpl->setCurrentBlock("confirm");
	$tpl->setVariable("ACTION_DELETE","mail_frameset.php?mobj_id=$_GET[mobj_id]");
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