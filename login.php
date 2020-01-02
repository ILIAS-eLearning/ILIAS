<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* login script for ilias
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias-layout
*/


// jump to setup if ILIAS3 is not installed
if (!file_exists(getcwd() . "/ilias.ini.php")) {
    header("Location: ./setup/setup.php");
    exit();
}

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setCmd('showLoginPageOrStartupPage');
$ilCtrl->callBaseClass();
$ilBench->save();

exit;
