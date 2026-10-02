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
 * Registration endpoint of ILIAS as LTI Advantage platform: a tool registered through Dynamic Registration
 * posts its configuration here, with the registration token ILIAS gave it, see
 * ilLTIAdvantagePlatformRegistration. Its URL is the one of earlier releases.
 *
 * Errors follow RFC 7591. The reason goes to the log, and neither the response nor the log shows the token.
 */

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

ilContext::init(ilContext::CONTEXT_SCORM);

global $DIC;

$log = $DIC->logger()->forComponent('lti');
$request = $DIC->http()->request();

if (strtoupper($request->getMethod()) !== 'POST') {
    [$status, $answer] = [405, ['error' => 'invalid_request', 'error_description' => 'The registration is posted.']];
} else {
    try {
        [$status, $answer] = ilLTIAdvantagePlatformRegistration::register(
            $request->getHeaderLine('Authorization'),
            (string) $request->getBody()
        );
    } catch (Throwable $e) {
        $log->error($e->getMessage());
        [$status, $answer] = [500, ['error' => 'server_error']];
    }
}

if ($status === 200) {
    $log->info('LTI Advantage tool registered through Dynamic Registration: ' . $answer['client_name']);
} else {
    $log->warning('LTI Advantage tool registration refused: ' . ($answer['error_description'] ?? $answer['error']));
}

ilLTIAdvantageResponse::json($status, $answer, ['Cache-Control' => 'no-store']);
