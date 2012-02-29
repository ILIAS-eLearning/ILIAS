<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* registration form for new users
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_STARTUP);

include_once "include/inc.header.php";

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->setCmd("jumpToRegistration");
$ilCtrl->callBaseClass();
$ilBench->save();


?>