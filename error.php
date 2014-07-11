<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$tpl->addBlockFile("CONTENT", "content", "tpl.error.html");

// #13515 - link back to "system" [see ilWebAccessChecker::sendError()]
$nd = $tree->getNodeData(ROOT_FOLDER_ID);
$txt = $nd['title'] == 'ILIAS' ? $lng->txt('repository') : $nd['title'];
$tpl->SetCurrentBlock("ErrorLink");
$tpl->SetVariable("TXT_LINK", $txt);
$tpl->SetVariable("LINK", ILIAS_HTTP_PATH. '/ilias.php?baseClass=ilRepositoryGUI&amp;client_id='.CLIENT_ID);
$tpl->ParseCurrentBlock();

$tpl->setCurrentBlock("content");
$tpl->setVariable("ERROR_MESSAGE",($_SESSION["failure"]));
$tpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_failure.png"));
$tpl->parseCurrentBlock();

ilSession::clear("referer");
ilSession::clear("message");
$tpl->show();
?>