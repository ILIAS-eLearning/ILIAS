<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "include/inc.header.php";
$tpl->addBlockFile("CONTENT", "content", "tpl.error.html");

$tpl->setCurrentBlock("content");
$tpl->setVariable("ERROR_MESSAGE",($_SESSION["failure"]));
$tpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_failure.gif"));
$tpl->parseCurrentBlock();

ilSession::clear("referer");
ilSession::clear("message");
$tpl->show();
?>