<?php
/**
* forums_threads_new
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/ilias_header.inc";
require_once "classes/class.Forum.php";

$frm = new Forum();

$lng->setSystemLanguage($ilias->ini->readVariable("language", "default"));

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_new.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK",$_GET["backurl"].".php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
$tpl->setVariable("BTN_TXT", $lng->txt("back"));
$tpl->parseCurrentBlock();

if (!$rbacsystem->checkAccess("write", $_GET["obj_id"], $_GET["parent"])) {
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->setVariable("TXT_FORUM_NEW_THREAD", $lng->txt("forums_new_thread"));

if ($_GET["cmd"] == "newthread")
{		
	$formData = $_POST["formData"];
	
	// Check Formular-Daten
	$checkEmptyFields = array(
		$lng->txt("subject")   => $formData["subject"],
		$lng->txt("message")   => $formData["message"]	
	);
	$errors = TUtil::checkFormEmpty($checkEmptyFields);
	if ($errors != "")
	{
		$tpl->setVariable("TXT_FORM_FEEDBACK", $lng->txt("form_empty_fields")."<br>".$errors);
	}
	else
	{		
		$frm->setWhereCondition("top_frm_fk = ".$_GET["obj_id"]);
		$topicData = $frm->getOneTopic();	
		
		$newPost = $frm->generateThread($_GET["obj_id"], $_GET["parent"], $topicData["top_pk"], $_SESSION["AccountId"], $formData["subject"], $formData["message"]);
		
		$tpl->setVariable("TXT_FORM_FEEDBACK", $lng->txt("forums_thread_new_entry"));
	}
}

$tpl->setCurrentBlock("new_thread");
$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
$tpl->setVariable("TXT_MESSAGE", $lng->txt("message"));
$tpl->setVariable("SUBMIT", $lng->txt("submit"));
$tpl->setVariable("RESET", $lng->txt("reset"));
$tpl->setVariable("FORMACTION", basename($PHP_SELF)."?cmd=newthread&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&backurl=".$_GET["backurl"]);
$tpl->parseCurrentBlock("new_thread");


if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
	$tpl->setCurrentBlock("message2");
	$tpl->setVariable("MSG", urldecode( $_GET["message"]));
	$tpl->parseCurrentBlock();
}


$tpl->show();

?>