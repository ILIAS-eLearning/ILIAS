<?php

include_once "include/ilias_header.inc";
include_once "classes/class.Explorer2.php";

$ilias =& new ILIAS;

$tplContent = new Template("explorer.html",true,true);


$exp = new Explorer2($ilias,1);

//filter object types
$exp->addFilter("grp");
$exp->addFilter("lo");

//build html-output
$exp->setOutput(0);
$output = $exp->getOutput();

$tplContent->setVariable("EXPLORER",$output);
$tplContent->setVariable("EXPAND", "1");

include_once "include/ilias_footer.inc";
?>