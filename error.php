<?php
require_once "include/ilias_header.inc";

$tplContent = new Template("error.html",true,true);
$tplContent->setVariable("BACK",$_SESSION["referer"]);
$tplContent->setVariable("ERROR_MESSAGE",stripslashes($_GET["message"]));

session_unregister("referer");

$tplContent->show();
?>