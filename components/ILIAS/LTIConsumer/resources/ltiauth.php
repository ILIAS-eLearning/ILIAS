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

require_once("../vendor/composer/vendor/autoload.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
} else {
    $data = $_GET;
    if (isset($_GET['client_id'])) {
        unset($_GET['client_id']);
    }
}

function sanitizeJson(string $string)
{
    $string = preg_replace('/(\w+):(\w+)/', '"$1":"$2"', $string);
    $string = str_replace("'", '"', $string);
    $string = str_replace('{', '{', $string);
    $string = str_replace('}', '}', $string);
    return json_decode($string, true);
}

ilInitialisation::initILIAS();
global $DIC;
$scope = $data['scope'] ?? '';
$responseType = $data['response_type'] ?? '';
$redirectUri = $data['redirect_uri'] ?? '';
$clientId = $data['client_id'] ?? $data['id'] ?? '';
$state = $data['state'] ?? '';
$nonce = $data['nonce'] ?? '';
$ltiMessageHint = $data['lti_message_hint'] ?? '';
$loginHint = $data['login_hint'] ?? '';

$hint = null;
$deploymentId = null;
$provider_id = 0;
$childRefId = 0;
$refId = 0;


if (empty($ltiMessageHint)) {
    $DIC->http()->saveResponse(
        $DIC->http()->response()->withStatus(400)
    );
    try {
        $DIC->http()->sendResponse();
        $DIC->http()->close();
    } catch (\ILIAS\HTTP\Response\Sender\ResponseSendingException $e) {
        $DIC->http()->close();
    }
}

$parts = explode(":", $ltiMessageHint);
$isContentSelection = false;
$ref_id = '';
$il_client_id = '';
$redirect_uri = '';
if (count($parts) === 2) {
    [$ref_id, $il_client_id] = $parts;
} elseif (count($parts) === 3) {
    [$first, $second, $third] = $parts;
    $il_client_id = $third;
    $ref_id = explode(",", $second)[0];
} else {
    $isContentSelection = true;
    [$ref_id, $il_client_id, $redirect_uri] = $parts;
}

ilSession::set('lti13_login_data', $data);

if ($isContentSelection) {
    $url = "../../../" . base64_decode($redirect_uri);
} else {
    $url = "../../../goto.php?target=lti_" . $ref_id . "&client_id=" . $il_client_id;
}

function buildSameSiteNoneSessionCookieHeader(): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE || session_id() === '') {
        return null;
    }

    $cookie_params = session_get_cookie_params();
    if (!(bool) ($cookie_params['secure'] ?? false)) {
        return null;
    }

    $cookie_parts = [
        rawurlencode(session_name()) . '=' . rawurlencode(session_id()),
        'Path=' . (string) ($cookie_params['path'] ?? '/'),
        'Secure',
        'SameSite=None'
    ];

    $domain = (string) ($cookie_params['domain'] ?? '');
    if ($domain !== '') {
        $cookie_parts[] = 'Domain=' . $domain;
    }
    if ((bool) ($cookie_params['httponly'] ?? true)) {
        $cookie_parts[] = 'HttpOnly';
    }

    return implode('; ', $cookie_parts);
}

$response = $DIC->http()->response()
    ->withStatus(302)
    ->withAddedHeader('Location', $url);

$session_cookie_header = buildSameSiteNoneSessionCookieHeader();
if ($session_cookie_header !== null) {
    $response = $response->withAddedHeader('Set-Cookie', $session_cookie_header);
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
