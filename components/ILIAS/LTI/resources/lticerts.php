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
use ILIAS\HTTP\Response\ResponseHeader;

/**
 * JSON Web Key Set of ILIAS as LTI Advantage platform and tool: tools and platforms verify the messages and
 * tokens ILIAS signs with it. Its URL must not change: updated installations gave it to their tools.
 */

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

ilContext::init(ilContext::CONTEXT_SCORM);

global $DIC;

try {
    $status = 200;
    $body = ilLTIAdvantageKeyPair::getJwks();
} catch (ilException $e) {
    $DIC->logger()->forComponent('lti')->error($e->getMessage());
    $status = 500;
    $body = ['error' => 'server_error'];
}

$DIC->http()->saveResponse(
    $DIC->http()->response()
        ->withStatus($status)
        ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json; charset=utf-8')
        ->withBody(Streams::ofString(json_encode($body, JSON_UNESCAPED_SLASHES)))
);
$DIC->http()->sendResponse();
$DIC->http()->close();
