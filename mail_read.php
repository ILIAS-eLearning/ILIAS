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
* mail
*
* @author Peter Gabriel <pgabriel@databay.de>
* @author Eva Wenzl <ewenzl@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilMail.php";

$lng->loadLanguageModule("mail");

//get the mail from user
$umail = new ilMail($_SESSION["AccountId"]);

// catch hack attempts
if (!$rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
}

$umail->markRead(array($_GET["mail_id"]));

$mail_data = $umail->getMail($_GET["mail_id"]);

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_read.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->setCurrentBlock("header_image");
$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mail_b.gif"));
$tpl->parseCurrentBlock();
$tpl->setVariable("HEADER",$lng->txt("mail_mails_of"));
setLocator($_GET["mobj_id"],'mail.php',$_SESSION["AccountId"],"");

// DOWNLOAD FILE
if($_POST["cmd"] || $_GET["cmd"] == "download")
{
	if($_POST["filename"] || $_GET["filename"])
	{
		if(isset($_POST["cmd"]["download"]) || $_GET["cmd"] == "download")
		{
			$filename = ($_POST["filename"])
				? $_POST["filename"]
				: $_GET["filename"];
			
			require_once "classes/class.ilFileDataMail.php";
			
			$mfile = new ilFileDataMail($_SESSION["AccountId"]);
			if(!$path = $mfile->getAttachmentPath($filename, $_GET["mail_id"]))
			{
				sendInfo("Error reading file!");
			}
			else
			{
				ilUtil::deliverFile($path, $filename);
			}
		}
	}
}
					
include "./include/inc.mail_buttons.php";

//buttons
$tplbtn = new ilTemplate("tpl.buttons.html", true, true);
if($mail_data["sender_id"])
{
	$tplbtn->setCurrentBlock("btn_cell");
	$tplbtn->setVariable("BTN_LINK","./mail_new.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]&type=reply");
	$tplbtn->setVariable("BTN_TXT", $lng->txt("reply"));
	$tplbtn->parseCurrentBlock();
}
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_new.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]&type=forward");
$tplbtn->setVariable("BTN_TXT", $lng->txt("forward"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail_print.php?mail_id=".$_GET["mail_id"]);
$tplbtn->setVariable("BTN_TXT", $lng->txt("print"));
$tplbtn->setVariable("BTN_TARGET","target=\"_blank\"");
$tplbtn->parseCurrentBlock();
if($mail_data["sender_id"])
{
	$tplbtn->setCurrentBlock("btn_cell");
	$tplbtn->setVariable("BTN_LINK", "mail_addressbook.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]&type=add");
	$tplbtn->setVariable("BTN_TXT", $lng->txt("mail_add_to_addressbook"));
	$tplbtn->parseCurrentBlock();
}
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK", "mail.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]");
$tplbtn->setVariable("BTN_TXT", $lng->txt("delete"));
$tplbtn->parseCurrentBlock();

$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS2",$tplbtn->get());
$tpl->setVariable("ACTION","mail_read.php?mobj_id=$_GET[mobj_id]&mail_id=$_GET[mail_id]");

// SET MAIL DATA
$counter = 1;
// FROM
$tpl->setVariable("TXT_FROM", $lng->txt("from"));

$tmp_user = new ilObjUser($mail_data["sender_id"]);
#$tmp_user =& ilObjectFactory::getInstanceByObjId($mail_data["sender_id"],false);

$tpl->setVariable("FROM", $tmp_user->getFullname());
$tpl->setCurrentBlock("pers_image");
$tpl->setVariable("IMG_SENDER", $tmp_user->getPersonalPicturePath("xsmall"));
$tpl->setVariable("ALT_SENDER", $tmp_user->getFullname());
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("adm_content");

if(!($login = $tmp_user->getLogin()))
{
	$login = $mail_data["import_name"]." (".$lng->txt("imported").")";
}
$tpl->setVariable("MAIL_LOGIN",$login);
$tpl->setVariable("CSSROW_FROM",++$counter%2 ? 'tblrow1' : 'tblrow2');
// TO
$tpl->setVariable("TXT_TO", $lng->txt("mail_to"));
$tpl->setVariable("TO", $mail_data["rcp_to"]);
$tpl->setVariable("CSSROW_TO",(++$counter)%2 ? 'tblrow1' : 'tblrow2');

// CC
if($mail_data["rcp_cc"])
{
	$tpl->setCurrentBlock("cc");
	$tpl->setVariable("TXT_CC",$lng->txt("cc"));
	$tpl->setVariable("CC",$mail_data["rcp_cc"]);
	$tpl->setVariable("CSSROW_CC",(++$counter)%2 ? 'tblrow1' : 'tblrow2');
	$tpl->parseCurrentBlock();
}
// SUBJECT
$tpl->setVariable("TXT_SUBJECT",$lng->txt("subject"));
$tpl->setVariable("SUBJECT",htmlspecialchars($mail_data["m_subject"]));
$tpl->setVariable("CSSROW_SUBJ",(++$counter)%2 ? 'tblrow1' : 'tblrow2');

// DATE
$tpl->setVariable("TXT_DATE", $lng->txt("date"));
$tpl->setVariable("DATE", ilFormat::formatDate($mail_data["send_time"]));
$tpl->setVariable("CSSROW_DATE",(++$counter)%2 ? 'tblrow1' : 'tblrow2');

// ATTACHMENTS
if($mail_data["attachments"])
{
	$tpl->setCurrentBlock("attachment");
	$tpl->setCurrentBlock("a_row");
	$counter = 1;
	foreach($mail_data["attachments"] as $file)
	{
		$tpl->setVariable("A_CSSROW",++$counter%2 ? 'tblrow1' : 'tblrow2');
		$tpl->setVariable("FILE",$file);
		$tpl->setVariable("FILE_NAME",$file);
		$tpl->parseCurrentBlock();
	}
	$tpl->setVariable("TXT_ATTACHMENT",$lng->txt("attachments"));
	$tpl->setVariable("TXT_DOWNLOAD",$lng->txt("download"));
	$tpl->parseCurrentBlock();
}

// MESSAGE
$tpl->setVariable("TXT_MESSAGE", $lng->txt("message"));
$tpl->setVariable("MAIL_MESSAGE", nl2br(ilUtil::makeClickable($mail_data["m_message"])));
//$tpl->setVariable("MAIL_MESSAGE", nl2br(ilUtil::makeClickable(htmlspecialchars($mail_data["m_message"]))));

$tpl->show();
?>
