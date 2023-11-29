<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

if (!file_exists(getcwd() . '/ilias.ini.php')) {
    exit();
}

ilContext::init(ilContext::CONTEXT_SAML);

ilInitialisation::initILIAS();

/** @var ILIAS\DI\Container $DIC */
$DIC->ctrl()->setTargetScript('ilias.php');
// @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
// $DIC->ctrl()->setCmd('doSamlAuthentication');
$DIC->ctrl()->callBaseClass(ilStartUpGUI::class);
