<?php

chdir('../../');
try {
	require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
	$ilWebAccessChecker = new ilWebAccessChecker($_SERVER['REQUEST_URI']);
	$ilWebAccessChecker->checkAndDeliver();
} catch (ilWACException $e) {
	if ($ilWebAccessChecker->isImage()) {
		ilFileDelivery::deliverFileInline('./Services/WebAccessChecker/templates/images/access_denied.png');
		exit;
	}

	require_once('./Services/Init/classes/class.ilInitialisation.php');
	session_destroy();
	ilContext::init(ilContext::CONTEXT_WEB_ACCESS_CHECK);
	ilInitialisation::initILIAS();

	global $tpl;

	$tpl->setVariable('BASE', strstr($_SERVER['REQUEST_URI'], '/data', true) . '/');
	ilUtil::sendFailure($e->getMessage());
	$tpl->getStandardTemplate();
	$tpl->show();
}
?>
