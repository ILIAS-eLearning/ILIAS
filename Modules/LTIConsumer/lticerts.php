<?php declare(strict_types=1);
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *

*****************************************************************************/

/** @noRector */

chdir("../../");

include_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_SCORM);

require_once "./Services/Init/classes/class.ilInitialisation.php";
ilInitialisation::initILIAS();

include_once './Modules/LTIConsumer/src/lti13lib.php';

$ltilib = new lti13lib();

if (!empty($ltilib->verifyPrivateKey())) {
    echo "ERROR_OPEN_SSL_CONF";
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($ltilib->getJwks(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
