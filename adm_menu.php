<?php
require_once "include/inc.header.php";
require_once "classes/class.Explorer.php";


$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

$explorer = new Explorer("adm_object.php");

$explorer->setExpand($_GET["expand"]);
$explorer->addFilter("root");
$explorer->addFilter("cat");
$explorer->addFilter("grp");
$explorer->addFilter("crs");
$explorer->addFilter("le");
$explorer->addFilter("frm");
$explorer->addFilter("lo");
$explorer->addFilter("rolf");
$explorer->addFilter("adm");
$explorer->addFilter("lngf");
$explorer->addFilter("usrf");
$explorer->addFilter("objf");
$explorer->setFiltered(true);
$explorer->setOutput(0);

$output = $explorer->getOutput();

$tpl->setCurrentBlock("content");
$tpl->setVariable("EXPLORER",$output);
$tpl->setVariable("ACTION", "adm_menu.php?expand=".$_GET["expand"]);
$tpl->parseCurrentBlock();

$tpl->show();

?>