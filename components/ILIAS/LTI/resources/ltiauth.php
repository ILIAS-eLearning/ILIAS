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

use ceLTIc\LTI\Util;

/**
 * OpenID Connect authentication endpoint of ILIAS as LTI Advantage platform: a tool that received the login
 * of a launch asks here for the id_token, which celtic/lti signs and posts to the tool, see
 * ilLTIAdvantagePlatformConnection. Its URL must not change: updated installations gave it to their tools.
 */

// The request carries the client id ILIAS gave the tool, which ILIAS would take for the id of its own client.
// It is read from the query as it came.
unset($_GET['client_id']);

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

global $DIC;

$request = $DIC->http()->request();
parse_str($request->getUri()->getQuery(), $query);
$body = $request->getParsedBody();
Util::$requestParameters = array_merge($query, is_array($body) ? $body : []);

$log = $DIC->logger()->forComponent('lti');
try {
    $connection = ilLTIAdvantagePlatformConnection::forClientId((string) (Util::$requestParameters['client_id'] ?? ''));
} catch (ilException $e) {
    $log->error($e->getMessage());
    $connection = null;
}

// an error is only sent back to a redirect URI the tool registered
if ($connection === null || !$connection->isRedirectionUri((string) (Util::$requestParameters['redirect_uri'] ?? ''))) {
    $log->warning('LTI Advantage authentication request refused: unknown client id or redirect URI.');
    $DIC->http()->saveResponse($DIC->http()->response()->withStatus(400));
    $DIC->http()->sendResponse();
    $DIC->http()->close();
}

$connection->handleRequest();
