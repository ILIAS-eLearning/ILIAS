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
 * Registration URL of ILIAS as LTI Advantage tool: a platform of the administration opens it for Dynamic
 * Registration, see ilLTIAdvantageToolRegistration. The page it answers with tells whether ILIAS is registered
 * and lets the platform close its dialog.
 *
 * The page and the log say why a registration failed, and neither shows a token.
 */

require_once '../vendor/composer/vendor/autoload.php';
require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

ilContext::init(ilContext::CONTEXT_SCORM);

global $DIC;

$request = $DIC->http()->request();
$body = $request->getParsedBody();
$parameters = array_merge($request->getQueryParams(), is_array($body) ? $body : []);

$log = $DIC->logger()->forComponent('lti');
[$registered, $reason] = new ilLTIAdvantageToolRegistration()->register($parameters);
if (!$registered) {
    $log->warning('LTI Advantage Dynamic Registration of ILIAS failed: ' . $reason);
}

$lng = $DIC->language();
$lng->loadLanguageModule('lti');
$message = htmlspecialchars($lng->txt($registered ? 'lti_dyn_reg_tool_done' : 'lti_dyn_reg_tool_failed'), ENT_QUOTES);
$close = htmlspecialchars($lng->txt('close'), ENT_QUOTES);
$page = '<!DOCTYPE html><html lang="' . htmlspecialchars($lng->getLangKey(), ENT_QUOTES) . '"><head><meta charset="utf-8">'
    . '<title>ILIAS</title></head><body style="font-family: sans-serif"><p>' . $message . '</p>'
    . ($registered ? '' : '<p>' . htmlspecialchars($reason, ENT_QUOTES) . '</p>')
    . '<button type="button" onclick="(window.opener || window.parent).postMessage({subject: \'org.imsglobal.lti.close\'}, \'*\');">'
    . $close . '</button></body></html>';

ilLTIAdvantageResponse::send($registered ? 200 : 400, ilLTIAdvantageResponse::CONTENT_TYPE_HTML, $page, ['Cache-Control' => 'no-store']);
