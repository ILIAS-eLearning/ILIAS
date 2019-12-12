<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* registration confirmation script for ilias
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id:
*/

// jump to setup if ILIAS is not installed
if (!file_exists(getcwd() . '/ilias.ini.php')) {
    header('Location: ./setup/setup.php');
    exit();
}

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$ilCtrl->initBaseClass('ilStartUpGUI');
$ilCtrl->setCmd('confirmRegistration');
$ilCtrl->callBaseClass();

exit();
