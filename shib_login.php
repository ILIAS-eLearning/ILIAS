<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Shibboleth login script for ilias
 *
 * $Id$
 * @author  Lukas Haemmerle <haemmerle@switch.ch>
 * @package ilias-layout
 */

include_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SHIBBOLETH);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

if (!$_SERVER['HTTP_SHIB_APPLICATION_ID'] && !$_SERVER['Shib-Application-ID'] && !$_SERVER['REDIRECT_Shib_Application_ID']) {
    $message = "This file must be protected by Shibboleth, otherwise you cannot use Shibboleth authentication! Consult the <a href=\"Services/AuthShibboleth/README.SHIBBOLETH.txt\">documentation</a> on how to configure Shibboleth authentication properly.";
    $ilias->raiseError($message, $ilias->error_obj->WARNING);
}

// authentication is done here ->
$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setCmd('doShibbolethAuthentication');
$ilCtrl->callBaseClass();
