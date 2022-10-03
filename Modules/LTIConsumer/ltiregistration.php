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
// verify bearer Token
global $DIC;
$auth = $DIC->http()->request()->getHeader("Authorization");
if (count($auth) < 1) {
    ilObjLTIConsumer::sendResponseError(405, "missing Authorization header");
}
preg_match('/Bearer\s+(.+)$/i', $auth[0], $matches);
if (count($matches) != 2) {
    try {
        ilObjLTIConsumer::sendResponseError(405, "missing required Authorization Baerer token");
    } catch (\ILIAS\HTTP\Response\Sender\ResponseSendingException $e) {
        $DIC->http()->close();
    }
}

try {
    $token = $matches[1];
    $tokenObj = getTokenObject($token);
    $data = getData();
    $responseData = ilObjLTIConsumer::registerClient($data, $tokenObj);
    ilObjLTIConsumer::sendResponseJson($responseData);
} catch (Exception $e) {
    ilObjLTIConsumer::sendResponseError(500, "error in ltiregistration.php");
}

function getData(): array
{
    return json_decode(file_get_contents('php://input'), true);
}

function getTokenObject(string $token): object
{
    $keys = Firebase\JWT\JWK::parseKeySet(ilObjLTIConsumer::getJwks());
    return Firebase\JWT\JWT::decode($token, $keys);
}
