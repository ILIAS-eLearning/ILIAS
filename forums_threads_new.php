<?php
/**
* forums_threads_new
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.Forum.php";
require_once "classes/class.ilObject.php";
require_once "classes/class.ilObjForum.php";

$forumObj = new ilObjForum($_GET["ref_id"]);
$frm = new Forum();
$frm->setForumId($forumObj->getId());

$frm->setWhereCondition("top_frm_fk = ".$frm->getForumId());
$topicData = $frm->getOneTopic();

$tpl->setVariable("TXT_PAGEHEADLINE", $forumObj->getTitle());
$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_new.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
// display infopanel if something happened
infoPanel();

if (!$rbacsystem->checkAccess("write",$forumObj->getRefId()))
{
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

// ********************************************************************************
// build location-links
$tpl->touchBlock("locator_separator");
$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $lng->txt("forums_overview"));
$tpl->setVariable("LINK_ITEM", "forums.php?ref_id=".$forumObj->getRefId());
$tpl->parseCurrentBlock();

if (!$_GET["backurl"])
{
	$tpl->touchBlock("locator_separator");
	$tpl->setCurrentBlock("locator_item");
	$tpl->setVariable("ITEM", $lng->txt("forums_topics_overview").": ".$topicData["top_name"]);
	$tpl->setVariable("LINK_ITEM", "forums_threads_liste.php?ref_id=".$forumObj->getRefId());
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("locator_item");
$tpl->setVariable("ITEM", $topicData["top_name"].": ".$lng->txt("forums_new_thread"));

if (!$_GET["backurl"])
{
	$tpl->setVariable("LINK_ITEM", "forums_threads_new.php?ref_id=".$forumObj->getRefId());
}
else
{
	$tpl->setVariable("LINK_ITEM", "forums_threads_new.php?ref_id=".$forumObj->getRefId()."&backurl=".$_GET["backurl"]);
}

$tpl->parseCurrentBlock();

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
	
	$errors = TUtil::checkFormEmpty($checkEmptyFields);
	
	if ($errors != "")
	{
		sendInfo($lng->txt("form_empty_fields")." ".$errors);
	}
	else
	{		
		// build new thread
		$newPost = $frm->generateThread($topicData["top_pk"], $_SESSION["AccountId"], $formData["subject"], $formData["message"]);
		
		// Visit-Counter
		$frm->setDbTable("frm_data");
		$frm->setWhereCondition("top_pk = ".$topicData["top_pk"]);
		$frm->updateVisits($topicData["top_pk"]);
		// on success: change location
		$frm->setWhereCondition("thr_top_fk = '".$topicData["top_pk"]."' AND thr_subject = '".$formData["subject"]."' AND thr_num_posts = 1");		

		if (is_array($thrData = $frm->getOneThread()))
		{
			sendInfo($lng->txt("forums_thread_new_entry"),true);
			header("location: forums_threads_view.php?thr_pk=".$thrData["thr_pk"]."&ref_id=".$forumObj->getRefId());
			exit();
		} 
	}
}

$tpl->setCurrentBlock("new_thread");
$tpl->setVariable("TXT_REQUIRED_FIELDS", $lng->txt("required_field"));
$tpl->setVariable("TXT_SUBJECT", $lng->txt("forums_thread"));
$tpl->setVariable("TXT_MESSAGE", $lng->txt("forums_the_post"));
$tpl->setVariable("SUBMIT", $lng->txt("submit"));
$tpl->setVariable("RESET", $lng->txt("reset"));
$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=newthread&ref_id=".$forumObj->getRefId()."&backurl=".$_GET["backurl"]);
$tpl->parseCurrentBlock("new_thread");

$tpl->show();
?>
