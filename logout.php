<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* logout script for ilias
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_STARTUP);

require_once "include/inc.header.php";

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setCmd("showLogout");
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();
$ilBench->save();

exit;
?>