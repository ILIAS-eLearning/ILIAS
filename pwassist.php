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


require_once "include/inc.header.php";

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->setCmd("jumpToPasswordAssistance");
$ilCtrl->callBaseClass();
$ilBench->save();

exit;

?>