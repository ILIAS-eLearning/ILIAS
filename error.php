<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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