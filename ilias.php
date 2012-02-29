<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* ilias.php. main script.
*
* If you want to use this script your base class must be declared
* within modules.xml.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/

// this will disable authentication for login, logout, register, etc.
if($_REQUEST["baseClass"] == "ilStartUpGUI")
{
	include_once "Services/Context/classes/class.ilContext.php";
	ilContext::init(ilContext::CONTEXT_STARTUP);
}

require_once "./include/inc.header.php";

global $ilCtrl, $ilBench, $ilLog;

$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();
$ilBench->save();
?>
