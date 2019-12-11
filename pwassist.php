<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Password assistance facility for users who have forgotten their password
* or for users for whom no password has been assigned yet.
*
* @author Werner Randelshofer <wrandels@hsw.fhz.ch>
* @version $Id$
*
* @package ilias-core
*/

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setCmd("jumpToPasswordAssistance");
$ilCtrl->callBaseClass();
$ilBench->save();

exit;
