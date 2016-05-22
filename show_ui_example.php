<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

define("DEVMODE", 1);

require_once("./libs/composer/vendor/autoload.php");
include("./include/inc.header.php");


require_once("./Services/Context/classes/class.ilContext.php");
ilContext::init("ilContextUIShowcase");

require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

global $DIC;
$tpl = $DIC["tpl"];

$tpl->getStandardTemplate();

if (array_key_exists("which", $_GET)) {
	$which = $_GET["which"];
	if (preg_match("%(\w|/)+%", $which) !== 1) {
		$content = "Unknown example: $which";
	}
	else {
		require_once("./src/UI/examples/$which.php");
		$fn = str_replace("/", "_", $which);
		$content = $fn();
	}
}
else {
	$content = "Define an example via 'which' parameter to page.";
}

$tpl->setContent($content);

$tpl->show();
