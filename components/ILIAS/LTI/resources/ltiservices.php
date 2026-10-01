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

use ILIAS\Filesystem\Stream\Streams;

/**
 * Services endpoint of ILIAS as LTI Advantage platform, the Assignment and Grade Services and the Names and
 * Role Provisioning Services, see ilLTIAdvantagePlatformServiceRequest. The service is addressed by the path
 * after the script, as in earlier releases, whose URLs the tools keep.
 */

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

ilContext::init(ilContext::CONTEXT_SCORM);

global $DIC;

$request = $DIC->http()->request();

try {
    [$status, $headers, $body] = ilLTIAdvantagePlatformServiceRequest::handle(
        $request->getMethod(),
        (string) ($request->getServerParams()['PATH_INFO'] ?? ''),
        $request->getHeaderLine('Authorization'),
        $request->getHeaderLine('Content-Type'),
        $request->getQueryParams(),
        (string) $request->getBody()
    );
} catch (Throwable $e) {
    $DIC->logger()->forComponent('lti')->error($e->getMessage());
    [$status, $headers, $body] = [500, ['Content-Type' => 'application/json; charset=utf-8'], '{"error":"server_error"}'];
}

$response = $DIC->http()->response()->withStatus($status)->withHeader('Cache-Control', 'no-store');
foreach ($headers as $name => $value) {
    $response = $response->withHeader($name, $value);
}
$DIC->http()->saveResponse($response->withBody(Streams::ofString($body)));
$DIC->http()->sendResponse();
$DIC->http()->close();
