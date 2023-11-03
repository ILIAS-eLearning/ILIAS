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

ilInitialisation::initILIAS();
global $DIC;

if (strtoupper($DIC->http()->request()->getMethod()) !== "GET") {
    $DIC->http()->saveResponse(
        $DIC->http()->response()
            ->withStatus(400)
    );
}

$params = $DIC->http()->wrapper()->query();

$url = '';
$typeId = '';

if ($params->has('url')) {
    $url = $params->retrieve('url', $DIC->refinery()->kindlyTo()->string());
} else {
    ilObjLTIConsumer::sendResponseError(400, "missing required url parameter in request");
}
// optional
if ($params->has('typeid')) {
    $typeId = $params->retrieve('typeid', $DIC->refinery()->kindlyTo()->listOf($DIC->refinery()->kindlyTo()->string()));
}
// create jwt token
$clientId = ilObjLTIConsumer::getNewClientId();
$scope = ilObjLTIConsumer::REG_TOKEN_OP_NEW_REG;
if (!empty($typeId)) {
    // In the context of an update, the aud is the id of the type.
    $aud = strval($typeId);
    $scope = ilObjLTIConsumer::REG_TOKEN_OP_UPDATE_REG;
}
try {
    $now = time();
    $token = [
        "sub" => $DIC->user()->getId(),
        "aud" => $clientId,
        "scope" => $scope,
        "iat" => $now,
        "exp" => $now + 3600
    ];
    $privateKey = ilObjLTIConsumer::getPrivateKey();
    $regToken = Firebase\JWT\JWT::encode($token, $privateKey['key'], 'RS256', $privateKey['kid']);
    if ($params->has('custom_params')) {
        $customParams = urldecode($params->retrieve('custom_params', $DIC->refinery()->kindlyTo()->string()));
        ilSession::set('lti_dynamic_registration_custom_params', $customParams);
    }
    ilSession::set('lti_dynamic_registration_client_id', $clientId);
    header("Location: " . $url . "&openid_configuration=" . urlencode(ilObjLTIConsumer::getOpenidConfigUrl()) . "&registration_token=" . $regToken);
} catch (Exception $exception) {
    ilObjLTIConsumer::sendResponseError(500, "error in ltiregstart.php: " . $exception->getMessage());
}
