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

$tpl->addBlockFile("CONTENT", "content", "tpl.forums_threads_liste.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK",$_SESSION["backurl"].".php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]);
$tpl->setVariable("BTN_TXT", $lng->txt("back"));
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","forums_threads_new.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&backurl=forums_threads_liste");
$tpl->setVariable("BTN_TXT", $lng->txt("forums_new_thread"));
$tpl->parseCurrentBlock();

if (!$rbacsystem->checkAccess("write", $_GET["obj_id"], $_GET["parent"])) {
	$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
}

$tpl->setVariable("TXT_FORUM_THREADS", $lng->txt("forums_threads"));

$frm->setWhereCondition("top_frm_fk = ".$_GET["obj_id"]);
if (is_array($topicData = $frm->getOneTopic())) {
	
	$frm->setOrderField("thr_subject");
	$resThreads = $frm->getThreadList($topicData["top_pk"]);
	
	if ($resThreads->numRows() > 0)
	{
		$z = 0;
		while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$tpl->setCurrentBlock("threads_row");
			$rowCol = TUtil::switchColor($z,"tblrow2","tblrow1");
			$tpl->setVariable("ROWCOL", $rowCol);
			
			$tpl->setVariable("TITLE","<a href=\"forums_threads_view.php?thr_pk=".$thrData["thr_pk"]."&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&backurl=forums_threads_liste\">".$thrData["thr_subject"]."</a>");
			
			unset($author);
			$author = $frm->getModerator($thrData["thr_usr_id"]);	
			$tpl->setVariable("AUTHOR","<a href=\"forums_user_view?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&user=".$thrData["thr_usr_id"]."&backurl=forums_threads_liste\">".$author["surname"]."</a>"); 
			
			$tpl->setVariable("NUM_POSTS",$thrData["thr_num_posts"]);	
			
			$lpCont = "";				
			if ($thrData["thr_last_post"] != "") $lastPost = $frm->getLastPost($thrData["thr_last_post"]);	
			if (is_array($lastPost)) {				
				$lpCont = "<a href=\"forums_posts_reply.php?pos_pk=".$lastPost["pos_pk"]."&obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&backurl=forums_threads_liste\">".$lastPost["pos_message"]."</a><br>".$lng->txt("from")."&nbsp;";			
				$lpCont .= "<a href=\"forums_user_view?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&user=".$lastPost["pos_usr_id"]."&backurl=forums_threads_liste\">".$lastPost["surname"]."</a><br>";
				$lpCont .= $lastPost["pos_date"];				
			}
			$tpl->setVariable("LAST_POST", $lpCont);			
			
			$tpl->parseCurrentBlock("threads_row");
			$z ++;
		}
	}			
}
else
{
	$tpl->setCurrentBlock("threads_no");
	$tpl->setVAriable("TXT_MSG_NO_THREADS_AVAILABLE",$lng->txt("forums_threads_not_available"));
	$tpl->parseCurrentBlock("threads_no");
}

$thrPath = $frm->getForumPath($_GET["obj_id"], $_GET["parent"], $topicData["top_pk"]);

$tpl->setCurrentBlock("threadtable");
$tpl->setVariable("TXT_THREAD_PATH", $thrPath);
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_AUTHOR", $lng->txt("forums_thread_create"));
$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_posts"));
$tpl->setVariable("TXT_LAST_POST", $lng->txt("forums_last_post"));
$tpl->parseCurrentBlock("threadtable");


if ($_GET["message"])
{
    $tpl->addBlockFile("MESSAGE", "message2", "tpl.message.html");
	$tpl->setCurrentBlock("message2");
	$tpl->setVariable("MSG", urldecode( $_GET["message"]));
	$tpl->parseCurrentBlock();
}


$tpl->show();

?>