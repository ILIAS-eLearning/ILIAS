<?php
/**
* forums
*
* @author Wolfgang Merkens <wmerkens@databay.de>
* @version $Id$
*
* @package ilias
*/
require_once "./include/inc.header.php";
require_once "classes/class.ilForumExport.php";

$frmEx = new ilForumExport();

$lng->loadLanguageModule("forum");

// print
if ($_GET["print_thread"] > 0 || $_GET["print_post"] > 0)
{
	$tplEx = new ilTemplate("tpl.forums_export_print.html",true,true);
	$tplEx->setVariable("JSPATH",dirname($_SERVER["SCRIPT_FILENAME"]));
	
	// Thread drucken
	if ($_GET["print_thread"] > 0)
	{
		$frmEx->setWhereCondition("top_pk = ".$_GET["thr_top_fk"]);
		
		// get forum- and thread-data
		if (is_array($topicData = $frmEx->getOneTopic()))
		{
		
			$frmEx->setWhereCondition("thr_pk = ".$_GET["print_thread"]);
			$threadData = $frmEx->getOneThread();			
			
			// get first post of thread
			$first_node = $frmEx->getFirstPostNode($_GET["print_thread"]);	
			
			// get complete tree of thread
			$frmEx->setOrderField("frm_posts_tree.rgt");
			$subtree_nodes = $frmEx->getPostTree($first_node);
			$posNum = count($subtree_nodes);
			
			// headline
			$tplEx->setVariable("HEADLINE", $lng->txt("forum").": ".$topicData["top_name"]." > ".$lng->txt("forums_thread").": ".$threadData["thr_subject"]." > ".$lng->txt("forums_count_art").": ".$posNum);
			
			// generate post-dates
			foreach($subtree_nodes as $node)
			{			
					
					$tplEx->setCurrentBlock("posts_row");
					$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
					$tplEx->setVariable("ROWCOL", $rowCol);
					
					// get author data
					unset($author);
					$author = $frmEx->getUser($node["author"]);	
					$tplEx->setVariable("AUTHOR",$author->getLogin()); 
					
					// get create- and update-dates
					if ($node["update_user"] > 0)
					{
						$node["update"] = $frmEx->convertDate($node["update"]);
						unset($lastuser);
						$lastuser = $frmEx->getUser($node["update_user"]);					
						$tplEx->setVariable("POST_UPDATE","<br/>[".$lng->txt("edited_at").": ".$node["update"]." - ".strtolower($lng->txt("from"))." ".$lastuser->getLogin()."]");
					}
		
					$tplEx->setVariable("TXT_REGISTERED", $lng->txt("registered_since"));
					$tplEx->setVariable("REGISTERED_SINCE",$frmEx->convertDate($author->getCreateDate()));
		
					$numPosts = $frmEx->countUserArticles($author->id);
					$tplEx->setVariable("TXT_NUM_POSTS", $lng->txt("forums_posts"));
					$tplEx->setVariable("NUM_POSTS",$numPosts);
					
					// prepare post
					$node["message"] = $frmEx->prepareText($node["message"]);
							
					$tplEx->setVariable("TXT_CREATE_DATE",$lng->txt("forums_thread_create_date"));
					$tplEx->setVariable("POST_DATE",$frmEx->convertDate($node["create_date"]));
					$tplEx->setVariable("SPACER","<hr noshade width=100% size=1 align='center'>");			
					$tplEx->setVariable("POST",nl2br($node["message"]));	
					$tplEx->parseCurrentBlock("posts_row");	
					
					$z ++;
					
			} // foreach($subtree_nodes as $node)
			
			$tplEx->setCurrentBlock("posttable");			
			$tplEx->setVariable("TXT_AUTHOR", $lng->txt("author"));		
			$tplEx->setVariable("TXT_POST", $lng->txt("forums_thread").": ".$threadData["thr_subject"]);	
			$tplEx->parseCurrentBlock("posttable");
			
		} // if (is_array($topicData = $frmEx->getOneTopic()))
		
	} // if ($_GET["print_thread"] > 0)
	
	// Post drucken
	elseif ($_GET["print_post"] > 0)
	{
		
		$frmEx->setWhereCondition("top_pk = ".$_GET["top_pk"]);
		
		// get forum- and thread-data
		if (is_array($topicData = $frmEx->getOneTopic()))
		{
			$frmEx->setWhereCondition("thr_pk = ".$_GET["thr_pk"]);
			$threadData = $frmEx->getOneThread();
			
			// headline
			$tplEx->setVariable("HEADLINE", $lng->txt("forum").": ".$topicData["top_name"]." > ".$lng->txt("forums_thread").": ".$threadData["thr_subject"]);
			
			$node = $frmEx->getOnePost($_GET["print_post"]);
			
			$tplEx->setCurrentBlock("posts_row");			
			$tplEx->setVariable("ROWCOL", "tblrow2");
			
			// get author data
			unset($author);
			$author = $frmEx->getUser($node["author"]);	
			$tplEx->setVariable("AUTHOR",$author->getLogin()); 
			
			// get create- and update-dates
			if ($node["update_user"] > 0)
			{
				$node["update"] = $frmEx->convertDate($node["update"]);
				unset($lastuser);
				$lastuser = $frmEx->getUser($node["update_user"]);					
				$tplEx->setVariable("POST_UPDATE","<br/>[".$lng->txt("edited_at").": ".$node["update"]." - ".strtolower($lng->txt("from"))." ".$lastuser->getLogin()."]");
			}

			$tplEx->setVariable("TXT_REGISTERED", $lng->txt("registered_since"));
			$tplEx->setVariable("REGISTERED_SINCE",$frmEx->convertDate($author->getCreateDate()));

			$numPosts = $frmEx->countUserArticles($author->id);
			$tplEx->setVariable("TXT_NUM_POSTS", $lng->txt("forums_posts"));
			$tplEx->setVariable("NUM_POSTS",$numPosts);
			
			// prepare post
			$node["message"] = $frmEx->prepareText($node["message"]);
					
			$tplEx->setVariable("TXT_CREATE_DATE",$lng->txt("forums_thread_create_date"));
			$tplEx->setVariable("POST_DATE",$frmEx->convertDate($node["create_date"]));
			$tplEx->setVariable("SPACER","<hr noshade width=100% size=1 align='center'>");			
			$tplEx->setVariable("POST",nl2br($node["message"]));	
			$tplEx->parseCurrentBlock("posts_row");	
			
			$tplEx->setCurrentBlock("posttable");			
			$tplEx->setVariable("TXT_AUTHOR", $lng->txt("author"));		
			$tplEx->setVariable("TXT_POST", $lng->txt("forums_thread").": ".$threadData["thr_subject"]);	
			$tplEx->parseCurrentBlock("posttable");
			
		}
		
		
		
		
		//$_GET["thr_pk"]
		//getOnePost($post)
		//$tpl->setVariable("REPLY_BUTTON","<a href=\"forums_threads_view.php?cmd=showreply&pos_pk=".$node["pos_pk"]."&ref_id=".$_GET["ref_id"]."&offset=".$Start."&orderby=".$_GET["orderby"]."&thr_pk=".$_GET["thr_pk"]."#".$node["pos_pk"]."\">".$lng->txt("reply")."</a>"); 
	}
	
	
}
// export html
elseif ($_POST["action"] == "html")
{
	$x=3;
}





// SET MAIL DATA
// FROM
$tplEx->setVariable("TXT_FROM", $lng->txt("from"));

$tplEx->show();

?>
