<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 */
global $DIC;

if (!file_exists(getcwd() . '/ilias.ini.php')) {
    header('Location: ./setup/setup.php');
    exit();
}

require_once 'Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SAML);

require_once 'Services/Init/classes/class.ilInitialisation.php';
ilInitialisation::initILIAS();

$DIC->ctrl()->initBaseClass('ilStartUpGUI');
$DIC->ctrl()->setCmd('doSamlAuthentication');
$DIC->ctrl()->setTargetScript('ilias.php');
$DIC->ctrl()->callBaseClass();
