<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Repository
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/

global $ilCtrl, $ilBench;

require_once "include/inc.header.php";
include_once "./Services/Repository/classes/class.ilRepositoryGUI.php";

$ilCtrl->setTargetScript("repository.php");

$ilBench->start("Core", "getCallStructure");
$ilCtrl->getCallStructure("ilrepositorygui");
$ilBench->stop("Core", "getCallStructure");

$repository_gui =& new ilRepositoryGUI();
//$repository_gui->prepareOutput();
//$repository_gui->executeCommand();
$ilCtrl->forwardCommand($repository_gui);

$ilBench->save();

?>
