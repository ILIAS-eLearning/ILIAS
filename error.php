<?php
include_once "include/ilias_header.inc";

$tplContent = new Template("error.html",true,true);
$tplContent->setVariable("BACK",$_SESSION["referer"]);
$tplContent->setVariable("ERROR_MESSAGE",$_GET["message"]);

session_unregister("referer");
include_once "include/ilias_footer.inc";
?>