<?php

/**
 * LTI launch target script
 *
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 */
include_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_LTI_PROVIDER);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

// debug lti parameters 
$GLOBALS['DIC']->logger()->lti()->dump($_REQUEST,  ilLogLevel::DEBUG);

// authentication is done here ->
$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setCmd('doLTIAuthentication');
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();

