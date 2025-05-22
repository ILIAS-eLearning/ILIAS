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

/**
 * LTI launch target script
 *
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 */
ilContext::init(ilContext::CONTEXT_LTI_PROVIDER);

ilInitialisation::initILIAS();

// authentication is done here ->
global $DIC;
// @todo: removed deprecated ilCtrl methods, this needs inspection by a maintainer.
// $DIC->ctrl()->setCmd('doLTIAuthentication');
$DIC->ctrl()->setTargetScript('ilias.php');
$DIC->ctrl()->callBaseClass('ilStartUpGUI');
