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
 * @author Stefan Meyer <smeyer@databay.de>
 * @version $Id$
 *
 * @package ilias
 */
require_once "./include/inc.header.php";
require_once "./include/inc.mail.php";
require_once "classes/class.ilObjUser.php";
require_once "classes/class.ilFormatMail.php";
require_once "classes/class.ilMailbox.php";
require_once "classes/class.ilFileDataMail.php";

$lng->loadLanguageModule("mail");

$_POST["attachments"] = $_POST["attachments"] ? $_POST["attachments"] : array();

$umail = new ilFormatMail($_SESSION["AccountId"]);
$mfile = new ilFileDataMail($_SESSION["AccountId"]);

// CHECK HACK
if (!$rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->WARNING);
}

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_new.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->setCurrentBlock("header_image");
$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_mail_b.gif"));
$tpl->parseCurrentBlock();
$tpl->setVariable("HEADER",$lng->txt("mail"));
infoPanel();

// LOCATOR
setLocator($_GET["mobj_id"],'mail_new.php',$_SESSION["AccountId"],"");

// SEND MESSAGE
if(isset($_POST["cmd"]["send"]))
{
	$f_message = $umail->formatLinebreakMessage(ilUtil::stripSlashes($_POST["m_message"]));
	$umail->setSaveInSentbox(true);
	if($error_message = $umail->sendMail($_POST["rcp_to"],$_POST["rcp_cc"],
										 $_POST["rcp_bcc"],ilUtil::stripSlashes($_POST["m_subject"]),$f_message,
										 $_POST["attachments"],$_POST["m_type"]))
	{
		sendInfo($error_message);
	}
	else
	{
		$mbox = new ilMailbox($_SESSION["AccountId"]);

		sendInfo($lng->txt("mail_message_send",true));
		header("location: mail.php?sent=1&mobj_id=".$mbox->getInboxFolder());
		exit;
	}
}

// SAVE IN DRAFT FOLDER
if(isset($_POST["cmd"]["save_message"]))
{
	if(!$_POST["m_subject"])
	{
		$_POST["m_subject"] = "No title";
	}
	$mbox = new ilMailbox($_SESSION["AccountId"]);
	$drafts_id = $mbox->getDraftsFolder();
	
	if(isset($_SESSION["draft"]))
	{
		$umail->updateDraft($drafts_id,$_POST["attachments"],$_POST["rcp_to"],$_POST["rcp_cc"],
								  $_POST["rcp_bcc"],$_POST["m_type"],$_POST["m_email"],
								  ilUtil::stripSlashes($_POST["m_subject"]),
								  ilUtil::stripSlashes($_POST["m_message"]),$_SESSION["draft"]);
		session_unregister("draft");
		sendInfo($lng->txt("mail_saved"),true);
		header("location: mail.php?mobj_id=".$mbox->getInboxFolder());
		exit;
	}
	else
	{
		$mbox = new ilMailbox($_SESSION["AccountId"]);
		$drafts_id = $mbox->getDraftsFolder();

		if($umail->sendInternalMail($drafts_id,$_SESSION["AccountId"],$_POST["attachments"],$_POST["rcp_to"],$_POST["rcp_cc"],
									$_POST["rcp_bcc"],'read',$_POST["m_type"],$_POST["m_email"],
									ilUtil::stripSlashes($_POST["m_subject"]),
									ilUtil::stripSlashes($_POST["m_message"]),$_SESSION["AccountId"]))
		{
			sendInfo($lng->txt("mail_saved"));
		}
		else
		{
			sendInfo($lng->txt("mail_send_error"));
		}
	}
}

// SEARCH RECIPIENTS
if(isset($_POST["cmd"]["rcp_to"]))
{
	$_SESSION["mail_search"] = 'to';
	sendInfo($lng->txt("mail_insert_query"));
}
if(isset($_POST["cmd"]["rcp_cc"]))
{
	$_SESSION["mail_search"] = 'cc';
	sendInfo($lng->txt("mail_insert_query"));
}
if(isset($_POST["cmd"]["rcp_bc"]))
{
	$_SESSION["mail_search"] = 'bc';
	sendInfo($lng->txt("mail_insert_query"));
}

// EDIT ATTACHMENTS
if(isset($_POST["cmd"]["edit"]))
{
	$umail->savePostData($_SESSION["AccountId"],$_POST["attachments"],
						 $_POST["rcp_to"],$_POST["rcp_cc"],$_POST["rcp_bcc"],$_POST["m_type"],
						 $_POST["m_email"],
						 ilUtil::stripSlashes($_POST["m_subject"]),
						 ilUtil::stripSlashes($_POST["m_message"]));
	header("location: mail_attachment.php?mobj_id=$_GET[mobj_id]");
}

// SEARCH BUTTON CLICKED
if(isset($_POST["cmd"]["search"]))
{
	$umail->savePostData($_SESSION["AccountId"],$_POST["attachments"],$_POST["rcp_to"],
						 $_POST["rcp_cc"],$_POST["rcp_bcc"],$_POST["m_type"],
						 $_POST["m_email"],
						 ilUtil::stripSlashes($_POST["m_subject"]),
						 ilUtil::stripSlashes($_POST["m_message"]));
	// IF NO TYPE IS GIVEN SEARCH IN BOTH 'system' and 'addressbook'
	if(!$_POST["type_system"] and !$_POST["type_addressbook"])
	{
		$_POST["type_system"] = $_POST["type_addressbook"] = 1;
	}
	$get = '';
	if($_POST["type_system"])
	{
		$get .= "&system=1";
	}
	if($_POST["type_addressbook"])
	{
		$get .= "&addressbook=1";
	}
	if(strlen(trim($_POST['search'])) < 3)
	{
		$lng->loadLanguageModule('search');
		sendInfo($lng->txt('search_minimum_three'));
		unset($_POST['cmd']);
		$_POST['cmd']['rcp_to'] = true;
	}
	else
	{
		header("location: mail_search.php?mobj_id=$_GET[mobj_id]&search=".urlencode($_POST["search"]).$get);
		exit();
	}
}
if(isset($_POST["cmd"]["search_cancel"]) or isset($_POST["cmd"]["cancel"]))
{
	unset($_SESSION["mail_search"]);
}

// BUTTONS
include "./include/inc.mail_buttons.php";


// FORWARD, REPLY, SEARCH

switch($_GET["type"])
{
	case 'reply':
		$mail_data = $umail->getMail($_GET["mail_id"]);
		$mail_data["m_subject"] = $umail->formatReplySubject();
		$mail_data["m_message"] = $umail->formatReplyMessage(); 
		$mail_data["m_message"] = $umail->appendSignature();
		// NO ATTACHMENTS FOR REPLIES
		$mail_data["attachments"] = array();
		$mail_data["rcp_to"] = $umail->formatReplyRecipient();
		break;

	case 'search_res':
		$mail_data = $umail->getSavedData();
		if($_POST["search_name"])
		{
			$mail_data = $umail->appendSearchResult($_POST["search_name"],$_SESSION["mail_search"]);
		}
		unset($_SESSION["mail_search"]);
		break;

	case 'attach':
		$mail_data = $umail->getSavedData();
		break;

	case 'draft':
		$_SESSION["draft"] = $_GET["mail_id"];
		$mail_data = $umail->getMail($_GET["mail_id"]);
		break;

	case 'forward':
		$mail_data = $umail->getMail($_GET["mail_id"]);
		$mail_data["rcp_to"] = $mail_data["rcp_cc"] = $mail_data["rcp_bcc"] = '';
		$mail_data["m_subject"] = $umail->formatForwardSubject();
		$mail_data["m_message"] = $umail->appendSignature();
		if(count($mail_data["attachments"]))
		{
			if($error = $mfile->adoptAttachments($mail_data["attachments"],$_GET["mail_id"]))
			{
				sendInfo($error);
			}
		}
		break;

	case 'new':
		$mail_data["rcp_to"] = $_GET['rcp_to'];
		$mail_data["m_message"] = $umail->appendSignature();
		break;

	case 'role':

		if(is_array($_POST['roles']))
		{
			$mail_data['rcp_to'] = implode(',',$_POST['roles']);
		}
		elseif(is_array($_SESSION['mail_roles']))
		{
			$mail_data['rcp_to'] = implode(',',$_SESSION['mail_roles']);
		}

		$mail_data['m_message'] = $_POST["additional_message_text"].chr(13).chr(10).$umail->appendSignature();
		$_POST["additional_message_text"] = "";
		$_SESSION['mail_roles'] = "";
		break;

	case 'address':
		$mail_data["rcp_to"] = urldecode($_GET["rcp"]);
		break;

	default:
		// GET DATA FROM POST
		$mail_data = $_POST;
		break;
}
$tpl->setVariable("ACTION", "mail_new.php?mobj_id=$_GET[mobj_id]");

// SEARCH BLOCK
if(isset($_POST["cmd"]["rcp_to"]) or
   isset($_POST["cmd"]["rcp_cc"]) or
   isset($_POST["cmd"]["rcp_bc"]))
#   isset($_POST["cmd"][""] == $lng->txt("search"))
{
	$tpl->setCurrentBlock("search");
	$tpl->setVariable("TXT_SEARCH_FOR",$lng->txt("search_for"));
	$tpl->setVariable("TXT_SEARCH_SYSTEM",$lng->txt("mail_search_system"));
	$tpl->setVariable("TXT_SEARCH_ADDRESS",$lng->txt("mail_search_addressbook"));
	$tpl->setVariable("BUTTON_SEARCH",$lng->txt("search"));
	$tpl->setVariable("BUTTON_CANCEL",$lng->txt("cancel"));
}

// RECIPIENT
$tpl->setVariable("TXT_RECIPIENT", $lng->txt("mail_to"));
$tpl->setVariable("TXT_SEARCH_RECIPIENT", $lng->txt("search_recipient"));
$tpl->setVariable("BUTTON_TO",$lng->txt("mail_to_search"));
// CC
$tpl->setVariable("TXT_CC", $lng->txt("cc"));
$tpl->setVariable("TXT_SEARCH_CC_RECIPIENT", $lng->txt("search_cc_recipient"));
$tpl->setVariable("BUTTON_CC",$lng->txt("mail_cc_search"));
// BCC
$tpl->setVariable("TXT_BC", $lng->txt("bc"));
$tpl->setVariable("TXT_SEARCH_BC_RECIPIENT", $lng->txt("search_bc_recipient"));
$tpl->setVariable("BUTTON_BC",$lng->txt("mail_bc_search"));
// SUBJECT
$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));

// TYPE
$tpl->setVariable("TXT_TYPE", $lng->txt("type"));
$tpl->setVariable("TXT_NORMAL", $lng->txt("mail_intern"));
if(!is_array($mail_data["m_type"]) or (is_array($mail_data["m_type"]) and in_array('normal',$mail_data["m_type"])))
{
	$tpl->setVariable("CHECKED_NORMAL",'checked="checked"');
}

// ONLY IF SYSTEM MAILS ARE ALLOWED
if($rbacsystem->checkAccess("system_message",$umail->getMailObjectReferenceId()))
{
	$tpl->setCurrentBlock("system_message");
	$tpl->setVariable("SYSTEM_TXT_TYPE", $lng->txt("type"));
	$tpl->setVariable("TXT_SYSTEM", $lng->txt("system_message"));
	if(is_array($mail_data["m_type"]) and in_array('system',$mail_data["m_type"]))
	{
		$tpl->setVariable("CHECKED_SYSTEM",'checked="checked"');
	}
	$tpl->parseCurrentBlock();
}
	
// ONLY IF SMTP MAILS ARE ALLOWED
if($rbacsystem->checkAccess("smtp_mail",$umail->getMailObjectReferenceId()))
{
	$tpl->setCurrentBlock("allow_smtp");
	$tpl->setVariable("TXT_EMAIL", $lng->txt("email"));
	if(is_array($mail_data["m_type"]) and in_array('email',$mail_data["m_type"]))
	{
		$tpl->setVariable("CHECKED_EMAIL",'checked="checked"');
	}
	$tpl->parseCurrentBlock();
}

// ATTACHMENT
$tpl->setVariable("TXT_ATTACHMENT",$lng->txt("mail_attachments"));
// SWITCH BUTTON 'add' 'edit'
if($mail_data["attachments"])
{
	$tpl->setVariable("BUTTON_EDIT",$lng->txt("edit"));
}
else
{
	$tpl->setVariable("BUTTON_EDIT",$lng->txt("add"));
}

// MESSAGE
$tpl->setVariable("TXT_MSG_CONTENT", $lng->txt("message_content"));

// BUTTONS
$tpl->setVariable("TXT_SEND", $lng->txt("send"));
$tpl->setVariable("TXT_MSG_SAVE", $lng->txt("save_message"));

// MAIL DATA
$tpl->setVariable("RCP_TO", ilUtil::stripSlashes($mail_data["rcp_to"]));
$tpl->setVariable("RCP_CC", ilUtil::stripSlashes($mail_data["rcp_cc"]));
$tpl->setVariable("RCP_BCC",ilUtil::stripSlashes($mail_data["rcp_bcc"]));

$tpl->setVariable("M_SUBJECT",ilUtil::stripSlashes($mail_data["m_subject"]));

if(count($mail_data["attachments"]))
{
	$tpl->setCurrentBlock("files");
	$tpl->setCurrentBlock("hidden");
	foreach($mail_data["attachments"] as $data)
	{
		$tpl->setVariable("ATTACHMENTS",$data);
		$tpl->parseCurrentBlock();
	}
	$tpl->setVariable("ROWS",count($mail_data["attachments"]));
	$tpl->setVariable("FILES",implode("\n",$mail_data["attachments"]));
	$tpl->parseCurrentBlock();
}
$tpl->setVariable("M_MESSAGE",ilUtil::stripSlashes($mail_data["m_message"]));
$tpl->parseCurrentBlock();

$tpl->show();
?>
