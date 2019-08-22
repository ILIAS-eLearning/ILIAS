<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

try
{
	require_once("Services/Init/classes/class.ilInitialisation.php");
	ilInitialisation::initILIAS();
	$DIC->globalScreen()->tool()->context()->claim()->external();
    $local_tpl = new ilInitGlobalTemplate("tpl.main.html", true, true);
	$local_tpl->addBlockFile("CONTENT", "content", "tpl.error.html");
	$lng->loadLanguageModule("error");
	// #13515 - link back to "system" [see ilWebAccessChecker::sendError()]
	$nd  = $tree->getNodeData(ROOT_FOLDER_ID);
	$txt = $lng->txt('error_back_to_repository');
	$local_tpl->SetCurrentBlock("ErrorLink");
	$local_tpl->SetVariable("TXT_LINK", $txt);
	$local_tpl->SetVariable("LINK", ilUtil::secureUrl(ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilRepositoryGUI&amp;client_id=' . CLIENT_ID));
	$local_tpl->ParseCurrentBlock();

	$local_tpl->setCurrentBlock("content");
	$local_tpl->setVariable("ERROR_MESSAGE", ($_SESSION["failure"]));
	$tpl->setTitle($lng->txt('error_sry_error'));

	//$tpl->parseCurrentBlock();

	ilSession::clear("referer");
	ilSession::clear("message");
	//$local_tpl->printToStdout();
    $tpl->setContent($local_tpl->get());
    $tpl->printToStdout();
}
catch(Exception $e)
{
	if(defined('DEVMODE') && DEVMODE)
	{
		throw $e;
	}

	if (!($e instanceof \PDOException)) {
		die($e->getMessage());
	}
}
