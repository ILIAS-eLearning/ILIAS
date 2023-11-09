<?php

declare(strict_types=1);

/**
 * LTI launch target script
 *
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 */
ilContext::init(ilContext::CONTEXT_LTI_PROVIDER);

ilInitialisation::initILIAS();

// authentication is done here ->
global $DIC;
$DIC->ctrl()->setCmd('doLTIAuthentication');
$DIC->ctrl()->setTargetScript('ilias.php');
$DIC->ctrl()->callBaseClass('ilStartUpGUI');
