<?php

declare(strict_types=1);

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

/** @noRector */
chdir("../../");

require_once("Services/Init/classes/class.ilInitialisation.php");

ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();

try {
    $token = ilObjLTIConsumer::verifyToken();
    $data = json_decode(ilObjLTIConsumer::getRawData(), true);
    $responseData = ilObjLTIConsumer::registerClient($data, $token);
    ilObjLTIConsumer::sendResponseJson($responseData);
} catch (Exception $e) {
    ilObjLTIConsumer::sendResponseError(500, "error in ltiregistration.php");
}
