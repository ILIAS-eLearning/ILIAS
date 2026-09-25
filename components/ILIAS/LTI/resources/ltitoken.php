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

use ceLTIc\LTI\Jwt\Jwt;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;

/**
 * OAuth 2 token endpoint of ILIAS as LTI Advantage platform: a tool proves who it is with a client assertion
 * signed with its key and gets an access token for the services of ILIAS. celtic/lti checks the assertion
 * and signs the token, see ilLTIAdvantagePlatformConnection. Its URL must not change: updated installations
 * gave it to their tools.
 *
 * Errors follow RFC 6749. The reason stays in the log, and neither the response nor the log shows a token.
 */

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

ilContext::init(ilContext::CONTEXT_SCORM);

global $DIC;

$log = $DIC->logger()->forComponent('lti');
$refuse = static function (int $status, string $error, string $reason) use ($DIC, $log): never {
    $log->warning('LTI Advantage access token refused: ' . $reason);
    $DIC->http()->saveResponse(
        $DIC->http()->response()
            ->withStatus($status)
            ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json; charset=utf-8')
            ->withHeader('Cache-Control', 'no-store')
            ->withBody(Streams::ofString(json_encode(['error' => $error])))
    );
    $DIC->http()->sendResponse();
    $DIC->http()->close();
};

$request = $DIC->http()->request();
$body = $request->getParsedBody();
$body = is_array($body) ? $body : [];

if (strtoupper($request->getMethod()) !== 'POST') {
    $refuse(400, 'invalid_request', 'method ' . $request->getMethod());
}
if (($body['grant_type'] ?? '') !== 'client_credentials') {
    $refuse(400, 'unsupported_grant_type', 'grant type ' . (string) ($body['grant_type'] ?? ''));
}
if (($body['client_assertion_type'] ?? '') !== 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer'
    || !is_string($body['client_assertion'] ?? null)) {
    $refuse(400, 'invalid_request', 'no client assertion');
}
$scopes = array_intersect(
    explode(' ', (string) ($body['scope'] ?? '')),
    ilLTIAdvantagePlatformConnection::ACCESS_TOKEN_SCOPES
);
if ($scopes === []) {
    $refuse(400, 'invalid_scope', 'scope ' . (string) ($body['scope'] ?? ''));
}

// the assertion names the tool, whose key then verifies it
$assertion = Jwt::getJwtClient();
$client_id = $assertion->load($body['client_assertion']) ? (string) $assertion->getClaim('iss', '') : '';
try {
    $connection = ilLTIAdvantagePlatformConnection::forClientId($client_id);
} catch (ilException $e) {
    $log->error($e->getMessage());
    $refuse(500, 'server_error', 'no key');
}
if ($connection === null) {
    $refuse(401, 'invalid_client', 'unknown client id ' . $client_id);
}
if (!$connection->verifySignature()) {
    $refuse(401, 'invalid_client', 'client ' . $client_id . ': ' . $connection->reason);
}

$connection->sendAccessToken(ilLTIAdvantagePlatformConnection::ACCESS_TOKEN_SCOPES);
