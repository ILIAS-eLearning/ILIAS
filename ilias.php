<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * ilias.php. main script.
 * For changes please contact the maintainer in
 * Services/Init/maintenance.json
 */

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

/**
 * @var $DIC \ILIAS\DI\Container
 */
global $DIC, $ilBench;

$DIC->ctrl()->callBaseClass();
$ilBench->save();
