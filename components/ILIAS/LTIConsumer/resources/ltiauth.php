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

/** @noRector */
require_once("../vendor/composer/vendor/autoload.php");


/**
 * There is no way to process a $_GET Request with
 * a valid third-party client_id param in regular initILIAS
 */
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

ilInitialisation::initILIAS();

global $DIC;

$ltiMessageHint = $data['lti_message_hint'];

if (empty($ltiMessageHint)) {
    $DIC->http()->saveResponse(
        $DIC->http()->response()
        ->withStatus(400)
    );
    try {
        $DIC->http()->sendResponse();
        $DIC->http()->close();
    } catch (\ILIAS\HTTP\Response\Sender\ResponseSendingException $e) {
        $DIC->http()->close();
    }
}
$mh = explode(":", $ltiMessageHint);
$isContentSelection = false;
$ref_id = '';
$client_id = '';
$redirect_uri = '';
if (count($mh) == 2) { // launch message auth
    list($ref_id, $client_id) = explode(":", $ltiMessageHint);
} else { // contentSelection message auth
    $isContentSelection = true;
    list($ref_id, $client_id, $redirect_uri) = explode(":", $ltiMessageHint);
}

ilSession::set('lti13_login_data', $data);
if ($isContentSelection) {
    $url = "../../../" . base64_decode($redirect_uri);
} else {
    $url = "../../../goto.php?target=lti_" . $ref_id . "&client_id=" . $client_id;
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
