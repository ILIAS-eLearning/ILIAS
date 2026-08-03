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
 * There is no way to process a $_GET Request with
 * a valid third-party client_id param in regular initILIAS
 */

function sanitizeJson(string $string)
{
    $string = preg_replace('/(\w+):(\w+)/', '"$1":"$2"', $string);
    $string = str_replace("'", '"', $string);
    $string = str_replace('{', '{', $string);
    $string = str_replace('}', '}', $string);
    return json_decode($string, true);
}


if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $orig = new ArrayObject($_POST);
    $data = $orig->getArrayCopy();
} elseif (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
    $orig = new ArrayObject($_GET);
    $data = $orig->getArrayCopy();
    // early removing client_id from $_GET
    // otherwise the client_id is interpreted as ILIAS client_id
    // and client.ini.php will not be found
    if (isset($_GET['client_id'])) {
        unset($_GET['client_id']);
    }
} else {
    header($_SERVER["SERVER_PROTOCOL"] . " 405 Method Not Allowed", true, 405);
    exit;
}

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

global $DIC;
$scope = $data['scope'] ?? '';
$responseType = $data['response_type'] ?? '';
$redirectUri = $data['redirect_uri'] ?? '';
$clientId = $data['client_id'] ?? $data['id'] ?? '';
$state = $data['state'] ?? '';
$nonce = $data['nonce'] ?? '';
$ltiMessageHint = $data['lti_message_hint'] ?? '';
$loginHint = $data['login_hint'] ?? '';

$isDlMode = false;
$hint = null;
$deploymentId = null;
$provider_id = 0;
$childRefId = 0;
$refId = 0;

if (
    $scope === 'openid' &&
    $responseType === 'id_token' &&
    $redirectUri !== '' &&
    $clientId !== ''
) {
    $provider_id = ilLTIConsumeProvider::getProviderIdFromClientId($clientId);
    $provider = ilLTIConsumeProvider::getInstance($provider_id);

    $hint = sanitizeJson($ltiMessageHint);
    $expectedState = ilSession::get('lti13_deep_linking_state');
    $allowedRedirectUris = array_map('trim', explode(',', $provider->getRedirectionUris()));
    if (
        is_array($hint) &&
        isset($hint['deployment_id']) &&
        is_array($expectedState) &&
        ($expectedState['flow'] ?? '') === 'content_selection' &&
        is_string($expectedState['state'] ?? null) &&
        is_int($expectedState['created_at'] ?? null) &&
        $expectedState['created_at'] >= time() - 300 &&
        $expectedState['created_at'] <= time() &&
        is_string($state) &&
        hash_equals($expectedState['state'], $state) &&
        ($expectedState['provider_id'] ?? 0) === $provider->getId() &&
        (int) $hint['deployment_id'] === $provider->getId() &&
        in_array($redirectUri, $allowedRedirectUris, true)
    ) {

        $deploymentId = (int) $hint['deployment_id'];
        $childRefId = ilObjLTIConsumer::getRefIdOfConsumerByDeploymentId((string) $deploymentId);
        if ($childRefId !== null) {
            $refId = $DIC->repositoryTree()->getParentId($childRefId);
        }
        if ($refId > 0 && ($expectedState['ref_id'] ?? 0) === $refId) {
            $isDlMode = true;
            ilSession::clear('lti13_deep_linking_state');
        }
    }

}


if (empty($ltiMessageHint) || (is_array($hint) && !$isDlMode)) {
    $DIC->http()->saveResponse(
        $DIC->http()->response()->withStatus(400)
    );
    $DIC->http()->sendResponse();
    $DIC->http()->close();
}

if ($isDlMode) {
    if ($refId <= 0) {
        $DIC->http()->saveResponse(
            $DIC->http()->response()->withStatus(400)
        );
        $DIC->http()->sendResponse();
        $DIC->http()->close();
    }

    ilSession::set('lti13_login_data', $data);

    $DIC->ctrl()->setParameterByClass(ilObjLTIConsumerGUI::class, 'new_type', 'lti');
    $DIC->ctrl()->setParameterByClass(ilObjLTIConsumerGUI::class, 'provider_id', (string) $deploymentId);
    $DIC->ctrl()->setParameterByClass(ilObjLTIConsumerGUI::class, 'ref_id', (string) $refId);
    $url = $DIC->ctrl()->getLinkTargetByClass([ilRepositoryGUI::class, ilObjLTIConsumerGUI::class], 'contentSelectionRequest');

    $response = $DIC->http()->response()
        ->withStatus(302)
        ->withAddedHeader('Location', $url);

    $sessionCookieHeader = buildSameSiteNoneSessionCookieHeader();
    if ($sessionCookieHeader !== null) {
        $response = $response->withAddedHeader('Set-Cookie', $sessionCookieHeader);
    }

    $DIC->http()->saveResponse($response);

    try {
        $DIC->http()->sendResponse();
        $DIC->http()->close();
    } catch (\ILIAS\HTTP\Response\Sender\ResponseSendingException $e) {
        $DIC->http()->close();
    }

}

if (!$isDlMode) {
    ilSession::set('lti13_login_data', $data);
}

$parts = explode(":", $ltiMessageHint, 3);
$isContentSelection = false;
$ref_id = '';
$il_client_id = '';
$redirect_uri = '';
if (count($parts) === 2) {
    [$ref_id, $il_client_id] = $parts;
} elseif (count($parts) === 3) {
    $isContentSelection = true;
    [$ref_id, $il_client_id, $redirect_uri] = $parts;
} else {
    $DIC->http()->saveResponse(
        $DIC->http()->response()->withStatus(400)
    );
    $DIC->http()->sendResponse();
    $DIC->http()->close();
}

if ($isContentSelection) {
    $redirect_target = base64_decode($redirect_uri, true);
    if ($redirect_target === false || $redirect_target === '' ||
        str_starts_with($redirect_target, '/') || str_contains($redirect_target, '..') ||
        parse_url($redirect_target, PHP_URL_SCHEME) !== null ||
        parse_url($redirect_target, PHP_URL_HOST) !== null) {
        $DIC->http()->saveResponse(
            $DIC->http()->response()->withStatus(400)
        );
        $DIC->http()->sendResponse();
        $DIC->http()->close();
    }
    $url = "../../../" . $redirect_target;
} else {
    $url = "../../../goto.php?target=lti_" . $ref_id . "&client_id=" . $il_client_id;
}

function buildSameSiteNoneSessionCookieHeader(): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE || session_id() === '') {
        return null;
    }

    $cookieParams = session_get_cookie_params();
    $secure = (bool) ($cookieParams['secure'] ?? false);
    if (!$secure) {
        return null;
    }

    $cookieName = session_name();
    $cookieValue = session_id();
    $path = (string) ($cookieParams['path'] ?? '/');
    $domain = (string) ($cookieParams['domain'] ?? '');
    $httpOnly = (bool) ($cookieParams['httponly'] ?? true);

    $parts = [
        rawurlencode($cookieName) . '=' . rawurlencode($cookieValue),
        'Path=' . $path,
        'Secure',
        'SameSite=None'
    ];

    if ($domain !== '') {
        $parts[] = 'Domain=' . $domain;
    }
    if ($httpOnly) {
        $parts[] = 'HttpOnly';
    }

    return implode('; ', $parts);
}

$response = $DIC->http()->response()
    ->withStatus(302)
    ->withAddedHeader('Location', $url);

$sessionCookieHeader = buildSameSiteNoneSessionCookieHeader();
if ($sessionCookieHeader !== null) {
    $response = $response->withAddedHeader('Set-Cookie', $sessionCookieHeader);
}

$DIC->http()->saveResponse(
    $response
);

try {
    $DIC->http()->sendResponse();
    $DIC->http()->close();
} catch (\ILIAS\HTTP\Response\Sender\ResponseSendingException $e) {
    $DIC->http()->close();
}
