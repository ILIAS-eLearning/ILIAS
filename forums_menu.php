<?php
require_once "include/inc.header.php";
require_once "classes/class.ilForumExplorer.php";


$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$exp = new ilForumExplorer("forums_threads_view.php?thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]",$_GET["thr_pk"]);
$exp->setTargetGet("pos_pk");

if ($_GET["fexpand"] == "")
{
	$forum = new ilForum();
	$tmp_array = $forum->getFirstPostNode($_GET["thr_pk"]);
	$expanded = $tmp_array["id"];
}
else
	$expanded = $_GET["fexpand"];
	
$exp->setExpand($expanded);

//build html-output
$exp->setOutput(0);
$output = $exp->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "forums_menu.php?fexpand=".$_GET["fexpand"]."&thr_pk=$_GET[thr_pk]&ref_id=$_GET[ref_id]");
$tpl->parseCurrentBlock();

$tpl->show();
?>
