<?php
require_once "include/inc.header.php";
require_once "classes/class.ilBookmarkExplorer.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$exp = new ilBookmarkExplorer("bookmarks.php",$_SESSION["AccountId"]);
$exp->setTargetGet("bmf_id");

if ($_GET["mexpand"] == "")
{
	$mtree = new ilTree($_SESSION["AccountId"]);
	$mtree->setTableNames('bookmark_tree','bookmark_data');
	$expanded = $mtree->readRootId();
}
else
	$expanded = $_GET["mexpand"];

$exp->setExpand($expanded);

//build html-output
$exp->setOutput(0);
$output = $exp->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "bookmark_tree.php?mexpand=".$_GET["mexpand"]);
$tpl->parseCurrentBlock();

$tpl->show();

?>
