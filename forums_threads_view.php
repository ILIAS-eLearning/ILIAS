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

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_view.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK",$_GET["backurl"].".php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
$tpl->setVariable("BTN_TXT", $lng->txt("back"));
$tpl->parseCurrentBlock();

if (!$rbacsystem->checkAccess("write", $_GET["obj_id"], $_GET["parent"])) {
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->setVariable("TXT_FORUM_ARTICLES", $lng->txt("forums_posts"));

$frm->setWhereCondition("top_frm_fk = ".$_GET["obj_id"]);
if (is_array($topicData = $frm->getOneTopic())) {
	
	$frm->setOrderField("pos_date DESC");
	$resPosts = $frm->getPostList($topicData["top_pk"], $_GET["thr_pk"]);
	
	if ($resPosts->numRows() > 0)
	{
		$z = 0;
		while ($posData = $resPosts->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tpl->setCurrentBlock("posts_row");
			$rowCol = TUtil::switchColor($z,"tblrow2","tblrow1");
			$tpl->setVariable("ROWCOL", $rowCol);
						
			unset($author);
			$author = $frm->getModerator($posData["pos_usr_id"]);	
			$tpl->setVariable("AUTHOR","<a href=\"forums_user_view?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&user=".$posData["pos_usr_id"]."&backurl=forums_threads_view&thr_pk=".$_GET["thr_pk"]."\">".$author["SurName"]."</a>"); 
			
			$posData["pos_date"] = $frm->convertDate($posData["pos_date"]);
			$tpl->setVariable("POST_DATE",$posData["pos_date"]);	
			
			$tpl->setVariable("POST",$posData["pos_message"]);	
			
			$tpl->setVariable("REPLY_BUTTON","<a href=\"forums_posts_reply.php?pos_pk=".$posData["pos_pk"]."&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&backurl=forums_threads_view&thr_pk=".$_GET["thr_pk"]."\">".$lng->txt("reply")."</a>"); 
			
			
			
			$tpl->parseCurrentBlock("posts_row");
			$z ++;
		}
	}			
}
else
{
	$tpl->setCurrentBlock("posts_no");
	$tpl->setVAriable("TXT_MSG_NO_POSTS_AVAILABLE",$lng->txt("forums_posts_not_available"));
	$tpl->parseCurrentBlock("posts_no");
}

$posPath = $frm->getForumPath($_GET["obj_id"], $_GET["parent"], $topicData["top_pk"], $_GET["thr_pk"]);

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