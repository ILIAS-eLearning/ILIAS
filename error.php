<?php
require_once "include/inc.header.php";
$tpl->addBlockFile("CONTENT", "content", "tpl.error.html");

$tpl->setCurrentBlock("content");
$tpl->setVariable("BACK",$_SESSION["referer"]);
$tpl->setVariable("ERROR_MESSAGE",($_SESSION["message"]));
$tpl->parseCurrentBlock();

session_unregister("referer");
unset($_SESSION["message"]);
$tpl->show();
?>