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

require_once("./src/UI/examples/Glyph1.php");
$content = Glyph1();

$tpl->setContent($content);

$tpl->show();
