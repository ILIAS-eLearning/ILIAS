<?php
require_once "include/ilias_header.inc";
require_once "classes/class.Explorer.php";

$tplContent = new Template("explorer.html",true,true);

$exp = new Explorer("lo_content.php");

$exp->setExpand($_GET["expand"]);
//filter object types
$exp->addFilter("cat");
$exp->addFilter("grp");
$exp->addFilter("crs");
$exp->addFilter("le");
$exp->setFiltered(true);

//build html-output
$exp->setOutput(0);
$output = $exp->getOutput();

$tplContent->setVariable("EXPLORER",$output);
$tplContent->setVariable("ACTION", "lo_menu.php?expand=".$_GET["expand"]);

$tplmain->setVariable("PAGECONTENT", $tplContent->get());
$tplmain->show();
?>