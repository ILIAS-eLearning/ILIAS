<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/** @noRector */
require_once("../vendor/composer/vendor/autoload.php");
ilContext::init(ilContext::CONTEXT_SHIBBOLETH);
ilInitialisation::initILIAS();
global $DIC;


// authentication is done here ->
// @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
// $DIC->ctrl()->setCmd('doOpenIdConnectAuthentication');
$DIC->ctrl()->setTargetScript('ilias.php');
$DIC->ctrl()->callBaseClass(ilStartUpGUI::class);