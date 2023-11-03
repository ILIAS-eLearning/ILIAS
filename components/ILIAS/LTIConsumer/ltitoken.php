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
 * us ++at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/** @noRector */

use ILIAS\Filesystem\Exception\IOException;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

chdir("../../../");

require_once("Services/Init/classes/class.ilInitialisation.php");

ilContext::init(ilContext::CONTEXT_SCORM);
ilInitialisation::initILIAS();

global $DIC;

ilObjLTIConsumer::getLogger()->debug("accesstoken request");

$gradeService = new ilLTIConsumerGradeService();

if (!empty(ilObjLTIConsumer::verifyPrivateKey())) {
    serverError(ilObjLTIConsumer::verifyPrivateKey());
}

if (strtoupper($DIC->http()->request()->getMethod()) !== "POST") {
    invalidRequest("wrong method " . $DIC->http()->request()->getMethod());
}

$params = $DIC->http()->wrapper()->query();
$post = $DIC->http()->wrapper()->post();

if (!$post->has('client_assertion') || !$post->has('client_assertion_type') || !$post->has('grant_type') || !$post->has('scope')) {
    invalidRequest("bad request: " . var_export($params, true) . "\n" . var_export($post, true));
}

$clientAssertion = $post->retrieve('client_assertion', $DIC->refinery()->kindlyTo()->string());
$clientAssertionType = $post->retrieve('client_assertion_type', $DIC->refinery()->kindlyTo()->string());
$grantType = $post->retrieve('grant_type', $DIC->refinery()->kindlyTo()->string());
$scope = $post->retrieve('scope', $DIC->refinery()->kindlyTo()->string());

if ($clientAssertionType != 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer' || $grantType != 'client_credentials') {
    invalidRequest("bad request: unsupported grant_type: " . $grantType);
}

$parts = explode('.', $clientAssertion);

if (count($parts) != 3) {
    invalidRequest("bad request: " . var_export($parts, true));
}

$payload = JWT::urlsafeB64Decode($parts[1]);
$claims = json_decode($payload, true);

if ($claims == null) {
    invalidRequest("bad request: no claims");
}

$clientId = $claims['sub'];
if (empty($clientId)) {
    invalidRequest("bad request: no claims");
}

$providerId = 0;
$provider = null;

try {
    $providerId = ilLTIConsumeProvider::getProviderIdFromClientId($clientId);
} catch (IOException $e) {
    invalidRequest(var_export($e, true));
}

try {
    $provider = new ilLTIConsumeProvider($providerId);
} catch (IOException $e) {
    serverError(var_export($e, true));
}

validateServiceToken($clientAssertion, $provider);

$scopes = array();
// ToDo: support for other services
$gradeService = new ilLTIConsumerGradeService();
$requestedscopes = explode(' ', $scope);
$scopes = array_intersect($requestedscopes, $gradeService->getPermittedScopes());

if (empty($scopes)) {
    invalidRequest("empty scopes");
}

sendAccessToken(implode(" ", $scopes), $provider);

function validateServiceToken(string $token, ilLTIConsumeProvider $provider): void
{
    try {
        ilObjLTIConsumer::getLogger()->debug("validateServiceToken");
        // ToDo: caching
        $jwks = file_get_contents($provider->getPublicKeyset());
        $keyset = json_decode($jwks, true);
        $keys = JWK::parseKeySet($keyset);
        $data = JWT::decode($token, $keys);
        //ilObjLTIConsumer::getLogger()->debug(var_export($data, TRUE));
        if ($provider->getClientId() != $data->iss || $provider->getClientId() != $data->sub) {
            invalidRequest("invalid clientId");
        }
    } catch (Exception $e) {
        serverError(var_export($e, true));
    }
}

function sendAccessToken(string $scopes, ilLTIConsumeProvider $provider): void
{
    ilObjLTIConsumer::getLogger()->debug("sendAccesToken");
    $now = time();
    $token = [
        "sub" => $provider->getClientId(),
        "iat" => $now,
        "exp" => $now + 3600,
        "imsglobal.org.security.scope" => $scopes
    ];
    try {
        $privateKey = ilObjLTIConsumer::getPrivateKey();
        $accessToken = JWT::encode($token, $privateKey['key'], 'RS256', $privateKey['kid']);
        $responseData = array(
            'access_token' => $accessToken,
            'token_type' => 'baerer',
            'expires_in' => 3600,
            'scope' => $scopes
        );
        ilObjLTIConsumer::sendResponseJson($responseData);
    } catch (Exception $e) {
        serverError(var_export($e, true));
    }
}

function serverError(string $log = ""): void
{
    if (!empty($log)) {
        ilObjLTIConsumer::getLogger()->error($log);
    }
    ilObjLTIConsumer::sendResponseError(500, json_encode(array('error' => "ERROR_OPEN_SSL_CONF")));
}

function invalidRequest(string $log = ""): void
{
    if (!empty($log)) {
        ilObjLTIConsumer::getLogger()->error($log);
    }
    ilObjLTIConsumer::sendResponseError(400, json_encode(array('error' => 'invalid_request')));
}
