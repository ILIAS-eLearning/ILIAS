<?php
require_once "include/ilias_header.inc";
require_once "classes/class.Explorer.php";

$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$explorer = new Explorer("adm_object.php");

$explorer->setExpand($_GET["expand"]);

$explorer->setOutput(0);

$output = $explorer->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "adm_menu.php?expand=".$_GET["expand"]);
$tpl->parseCurrentBlock();

$tpl->show();

?>