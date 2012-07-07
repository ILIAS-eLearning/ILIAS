<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$tpl->addBlockFile("CONTENT", "content", "tpl.error.html");

$tpl->setCurrentBlock("content");
$tpl->setVariable("ERROR_MESSAGE",($_SESSION["failure"]));
$tpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_failure.png"));
$tpl->parseCurrentBlock();

ilSession::clear("referer");
ilSession::clear("message");
$tpl->show();
?>