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

$frm = new Forum();

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_posts_reply.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK",$_GET["backurl"].".php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&thr_pk=".$_GET["thr_pk"]);
$tpl->setVariable("BTN_TXT", $lng->txt("back"));
$tpl->parseCurrentBlock();

if (!$rbacsystem->checkAccess("write", $_GET["obj_id"], $_GET["parent"])) {
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->setVariable("TXT_FORUM_REPLY", $lng->txt("forums_respond"));

if ($_GET["cmd"] == "replypost")
{		
	$formData = $_POST["formData"];
	
	// Check Formular-Daten
	$checkEmptyFields = array(
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
		
		$newPost = $frm->generatePost($_GET["obj_id"], $_GET["parent"], $topicData["top_pk"], $_GET["thr_pk"], $_SESSION["AccountId"], $formData["message"]);
		
		$tpl->setVariable("TXT_FORM_FEEDBACK", $lng->txt("forums_post_new_entry"));
	}
}

if ($newPost != "") $pos_pk = $newPost;
else $pos_pk = $_GET["pos_pk"];

if (is_array($posData = $frm->getOnePost($pos_pk))) {	
	
	$tpl->setCurrentBlock("posts_row");	
	$tpl->setVariable("ROWCOL", "tblrow2");
					
	unset($author);
	$author = $frm->getModerator($posData["pos_usr_id"]);	
	$tpl->setVariable("AUTHOR","<a href=\"forums_user_view?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&user=".$posData["pos_usr_id"]."&backurl=forums_posts_reply&pos_pk=".$_GET["pos_pk"]."\">".$author["SurName"]."</a>"); 
		
	$tpl->setVariable("POST_DATE",$posData["pos_date"]);	
	
	$tpl->setVariable("POST",$posData["pos_message"]);		
		
	$tpl->parseCurrentBlock("posts_row");		
			
}

$posPath = $frm->getForumPath($_GET["obj_id"], $_GET["parent"], $posData["pos_top_fk"], $posData["pos_thr_fk"]);

$tpl->setCurrentBlock("reply_post");
$tpl->setVariable("TXT_FORM_HEADER", $lng->txt("forums_your_reply"));
$tpl->setVariable("TXT_MESSAGE", $lng->txt("message"));
$tpl->setVariable("SUBMIT", $lng->txt("submit"));
$tpl->setVariable("RESET", $lng->txt("reset"));
$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?cmd=replypost&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&backurl=".$_GET["backurl"]."&pos_pk=".$_GET["pos_pk"]."&thr_pk=".$posData["pos_thr_fk"]);
$tpl->parseCurrentBlock("reply_post");

$tpl->setCurrentBlock("posttable");
$tpl->setVariable("TXT_POST_PATH", $posPath);
$tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
$tpl->setVariable("TXT_POST", $lng->txt("forums_the_post"));
$tpl->parseCurrentBlock("posttable");


if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
	$tpl->setCurrentBlock("message2");
	$tpl->setVariable("MSG", urldecode( $_GET["message"]));
	$tpl->parseCurrentBlock();
}


$tpl->show();

?>