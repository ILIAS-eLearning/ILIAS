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
 * OpenID configuration of ILIAS as LTI Advantage platform, which a tool reads when it registers through
 * Dynamic Registration, see ilLTIAdvantagePlatformRegistration. Its URL is the one of earlier releases.
 */

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

ilContext::init(ilContext::CONTEXT_SCORM);

global $DIC;

$DIC->http()->saveResponse(
    $DIC->http()->response()
        ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json; charset=utf-8')
        ->withBody(Streams::ofString(json_encode(
            ilLTIAdvantagePlatformRegistration::getOpenidConfiguration(),
            JSON_UNESCAPED_SLASHES
        )))
);
$DIC->http()->sendResponse();
$DIC->http()->close();
