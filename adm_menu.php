<?php

include_once "include/ilias_header.inc";
include_once "classes/class.Explorer.php";

$ilias =& new ILIAS;

$expanded = explode('|',$_GET["expand"]);

$tplContent = new Template("explorer.html",true,true);
$explorer = new Explorer($ilias);
$explorer->setOutput(1,0);

$output = $explorer->getOutput();
$tplContent->setVariable("EXPLORER",$output);
$tplContent->setVariable("EXPAND",$_GET["expand"]);

include_once "include/ilias_footer.inc";
?>