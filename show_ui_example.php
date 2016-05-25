<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

define("DEVMODE", 1);

require_once("./libs/composer/vendor/autoload.php");

require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

global $DIC;
$tpl = $DIC["tpl"];

$tpl->getStandardTemplate();

if (array_key_exists("which", $_GET)) {
	$which = $_GET["which"];
	$include = "./src/UI/examples/$which.php";
	if (preg_match("%(\w|/)+%", $which) !== 1
	|| !file_exists($include)) {
		$content = "Unknown example: $which";
	}
	else {
		require_once($include);
		$fn = str_replace("/", "_", $which);
		$content = $fn();
	}
}
else {
	$content = "Define an example via 'which' parameter to page.";
}

$tpl->setContent($content);

$tpl->show();
