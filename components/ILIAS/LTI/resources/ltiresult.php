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
 * Endpoint of the LTI 1.1 Basic Outcomes service: a tool posts here to read, replace or delete the result
 * of the object it was launched from. Its URL must not change: updated installations keep it.
 */

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

global $DIC;

$client_id = $DIC->http()->wrapper()->query()->retrieve(
    'client_id',
    $DIC->refinery()->byTrying([$DIC->refinery()->kindlyTo()->string(), $DIC->refinery()->always('')])
);

if ($client_id === '') {
    $log = $DIC->logger()->forComponent('lti');
    $log->error("HTTP/1.1 401 Authorization Required");
    header('HTTP/1.1 401 Authorization Required');
    exit;
}

ilContext::init(ilContext::CONTEXT_SCORM);

$log = $DIC->logger()->forComponent('lti');
$log->info("LTI result init successful");
$service = new ilLTIConsumerResultService();
$service->handleRequest();
