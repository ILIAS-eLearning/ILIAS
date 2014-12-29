<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$token = $_GET['token'];
$action = $_GET['action'];

require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
$na_utils = gevNAUtils::getInstance();

if ($action == "confirm") {
	$result = $na_utils->confirmWithToken($token);
}
elseif ($action == "deny") {
	$result = $na_utils->denyWithToken($token);
}
else {
	throw new Exception("na_confirmation.php: unknown '".$action."'.");
}

if (!$result) {
	$tpl->addBlockFile("CONTENT", "content", "tpl.error.html");

	$tpl->setCurrentBlock("content");
	$tpl->setVariable("ERROR_MESSAGE","Der von ihnen verwendete Link wurde bereits benutzt ".
									  "oder ist abgelaufen. Bitte wenden sie sich bei Fragen an ".
											  "<a href='mailto:bildungspunkte.de@generali.com' class='blue'>bildungspunkte.de@generali.com</a></b>.");
	$tpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_failure.png"));
	$tpl->parseCurrentBlock();
}
else {
	if ($action == "confirm") {
		$tpl->addBlockFile("CONTENT", "content", "tpl.error.html");

		$tpl->setCurrentBlock("content");
		$tpl->setVariable("ERROR_MESSAGE", "Der Benutzeraccount ihres NAs wurde erfolgreich bestätigt.");
		$tpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_failure.png"));
		$tpl->parseCurrentBlock();
	}
	else {
		$tpl->addBlockFile("CONTENT", "content", "tpl.error.html");

		$tpl->setCurrentBlock("content");
		$tpl->setVariable("ERROR_MESSAGE", "Der Benutzeraccount ihres NAs wurde erfolgreich abgelehnt.");
		$tpl->setVariable("SRC_IMAGE", ilUtil::getImagePath("mess_failure.png"));
		$tpl->parseCurrentBlock();
	}
}

$tpl->show();

?>
