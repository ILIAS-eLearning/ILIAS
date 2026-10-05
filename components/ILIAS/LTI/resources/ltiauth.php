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
// the answer is always posted and never asks the user, whatever the tool says: tools set up for earlier
// releases of ILIAS may leave out response_mode and prompt, which the library requires, and earlier
// releases also took the client id as id
Util::$requestParameters['client_id'] ??= Util::$requestParameters['id'] ?? null;
Util::$requestParameters['response_mode'] = 'form_post';
Util::$requestParameters['prompt'] = 'none';

$log = $DIC->logger()->forComponent('lti');
try {
    $connection = ilLTIAdvantagePlatformConnection::forClientId((string) (Util::$requestParameters['client_id'] ?? ''));
} catch (ilException $e) {
    $log->error($e->getMessage());
    $connection = null;
}

// an error is only sent back to a redirect URI the tool registered
if ($connection === null || !$connection->isRedirectionUri((string) (Util::$requestParameters['redirect_uri'] ?? ''))) {
    $log->warning('LTI Advantage authentication request refused: unknown client id or redirect URI ({request})', [
        'request' => ilLTILibraryLogger::describe([
            'client_id' => Util::$requestParameters['client_id'] ?? null,
            'redirect_uri' => Util::$requestParameters['redirect_uri'] ?? null,
            'user_id' => Util::$requestParameters['login_hint'] ?? null,
            'hint' => Util::$requestParameters['lti_message_hint'] ?? null,
        ]),
    ]);
    ilLTIAdvantageResponse::send(400, '', '');
}

// the answer goes to the site of the tool, and the next launch or login of the same user may come from there too
if (!$DIC->user()->isAnonymous()) {
    ilLTISessionCookie::allowCrossSite();
}

register_shutdown_function($connection->logAuthenticationAnswer(...));
$connection->handleRequest();
