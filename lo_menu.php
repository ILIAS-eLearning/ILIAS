<?php
require_once "include/inc.header.php";
require_once "classes/class.Explorer.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$exp = new Explorer("lo_content.php");

if ($_GET["expand"] == "")
{
	$expanded = "1";
}
else
	$expanded = $_GET["expand"];
	
$exp->setExpand($expanded);
//filter object types
$exp->addFilter("root");
$exp->addFilter("cat");
$exp->addFilter("grp");
$exp->addFilter("crs");
$exp->addFilter("le");
$exp->setFiltered(true);

//build html-output
$exp->setOutput(0);
$output = $exp->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "lo_menu.php?expand=".$_GET["expand"]);
$tpl->parseCurrentBlock();

$tpl->show();

?>