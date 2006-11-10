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
* forums_threads_new
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilObjForum.php";

$lng->loadLanguageModule("forum");

$forumObj = new ilObjForum($_GET["ref_id"]);
$frm =& $forumObj->Forum;

$frm->setForumId($forumObj->getId());
$frm->setForumRefId($forumObj->getRefId());

$frm->setWhereCondition("top_frm_fk = ".$frm->getForumId());
$topicData = $frm->getOneTopic();

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.forums_threads_new.html");
$tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

$tpl->setCurrentBlock("header_image");
$tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_frm_b.gif"));
$tpl->parseCurrentBlock();
$tpl->setVariable("HEADER", $lng->txt("frm")." \"".$forumObj->getTitle()."\"");

// display infopanel if something happened
infoPanel();

if (!$rbacsystem->checkAccess("edit_post",$forumObj->getRefId()))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

require_once("classes/class.ilForumLocatorGUI.php");
$frm_loc =& new ilForumLocatorGUI();
$frm_loc->setRefId($_GET["ref_id"]);
$frm_loc->setForum($frm);
$frm_loc->display();



// ********************************************************************************

// form processing
if ($_GET["cmd"] == "newthread")
{
	$formData = $_POST["formData"];

	// check form-dates
	$checkEmptyFields = array(
		$lng->txt("subject")   => $formData["subject"],
		$lng->txt("message")   => $formData["message"]
	);

	$errors = ilUtil::checkFormEmpty($checkEmptyFields);
	if ($errors != "")
	{
		sendInfo($lng->txt("form_empty_fields")." ".$errors);
	}
	else
	{	
		
		// build new thread
		$newPost = $frm->generateThread($topicData["top_pk"], $_SESSION["AccountId"], 
			ilUtil::stripSlashes($formData["subject"]), ilUtil::stripSlashes($formData["message"]),$formData["notify"],$formData["notify_posts"],$formData["anonymize"]);
		
		// file upload
		if(isset($_FILES["userfile"]))
		{
			$tmp_file_obj =& new ilFileDataForum($forumObj->getId(),$newPost);
			$tmp_file_obj->storeUploadedFile($_FILES["userfile"]);
		}
		// end file upload		
		
		// Visit-Counter
		$frm->setDbTable("frm_data");
		$frm->setWhereCondition("top_pk = ".$topicData["top_pk"]);
		$frm->updateVisits($topicData["top_pk"]);
		// on success: change location
		$frm->setWhereCondition("thr_top_fk = '".$topicData["top_pk"]."' AND thr_subject = ".
								$ilDB->quote($formData["subject"])." AND thr_num_posts = 1");		

		if (is_array($thrData = $frm->getOneThread()))
		{
			#sendInfo($lng->txt("forums_thread_new_entry"),true);
			#header("location: forums_threads_liste.php?thr_pk=".$thrData["thr_pk"]."&ref_id=".$forumObj->getRefId());
			ilUtil::redirect('repository.php?ref_id='.$forumObj->getRefId());
		} 
	}
}

$tpl->setCurrentBlock("new_thread");
$tpl->setVariable("TXT_REQUIRED_FIELDS", $lng->txt("required_field"));
$tpl->setVariable("TXT_SUBJECT", $lng->txt("forums_thread"));
$tpl->setVariable("TXT_MESSAGE", $lng->txt("forums_the_post"));


include_once 'classes/class.ilMail.php';
$umail = new ilMail($_SESSION["AccountId"]);
// catch hack attempts
if ($rbacsystem->checkAccess("mail_visible",$umail->getMailObjectReferenceId()))
{
	$tpl->setCurrentBlock("notify");
	$tpl->setVariable("TXT_NOTIFY",$lng->txt("forum_direct_notification"));
	$tpl->setVariable("NOTIFY",$lng->txt("forum_notify_me_directly"));
	$tpl->parseCurrentBlock();
	if ($ilias->getSetting("forum_notification") != 0)
	{
		$tpl->setCurrentBlock("notify_posts");
		$tpl->setVariable("TXT_NOTIFY_POSTS",$lng->txt("forum_general_notification"));
		$tpl->setVariable("NOTIFY_POSTS",$lng->txt("forum_notify_me_generally"));
		$tpl->parseCurrentBlock();
	}
}
/*if ($frm->isAnonymized())
{
	$tpl->setCurrentBlock("anonymize");
	$tpl->setVariable("TXT_ANONYMIZE",$lng->txt("forum_anonymize"));
	$tpl->setVariable("ANONYMIZE",$lng->txt("forum_anonymize_desc"));
	$tpl->parseCurrentBlock();
}*/
$tpl->setVariable("SUBMIT", $lng->txt("submit"));
$tpl->setVariable("RESET", $lng->txt("reset"));
$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=newthread&ref_id=".$forumObj->getRefId()."&backurl=".$_GET["backurl"]);
$tpl->setVariable("TXT_NEW_TOPIC", $lng->txt("forums_new_thread"));

$tpl->setCurrentBlock("attachment");
$tpl->setVariable("TXT_ATTACHMENTS_ADD",$lng->txt("forums_attachments_add"));
$tpl->setVariable("BUTTON_UPLOAD",$lng->txt("upload"));
$tpl->parseCurrentBlock("attachment");

$tpl->parseCurrentBlock("new_thread");

$tpl->setVariable("TPLPATH", $tpl->vars["TPLPATH"]);

$tpl->show();
?>
