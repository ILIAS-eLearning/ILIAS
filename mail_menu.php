<?php
require_once "include/inc.header.php";
require_once "classes/class.ilMailExplorer.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$exp = new ilMailExplorer("mail.php",$_SESSION["AccountId"]);
$exp->setTargetGet("mobj_id");

if ($_GET["mexpand"] == "")
{
	$mtree = new ilTree($_SESSION["AccountId"]);
	$mtree->setTableNames('mail_tree','mail_obj_data');
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
$tpl->setVariable("ACTION", "mail_menu.php?mexpand=".$_GET["mexpand"]);
$tpl->parseCurrentBlock();

$tpl->show();

?>
