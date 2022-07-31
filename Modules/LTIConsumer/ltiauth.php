<?php declare(strict_types=1);

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

/** @noRector */
chdir("../../");
ilInitialisation::initILIAS();

/**
 * @var $DIC \ILIAS\DI\Container
 */
global $DIC;

$method = strtoupper($DIC->http()->request()->getMethod());

if ($method !== 'POST') {
    $DIC->http()->saveResponse(
        $DIC->http()->response()
        ->withStatus(405)
    );
    $DIC->http()->sendResponse();
    $DIC->http()->close();
}

$body = $DIC->http()->request()->getParsedBody();
$ltiMessageHint = $body['lti_message_hint'];
if (empty($ltiMessageHint)) {
    $DIC->http()->saveResponse(
        $DIC->http()->response()
        ->withStatus(400)
    );
    $DIC->http()->sendResponse();
    $DIC->http()->close();
}
list($ref_id, $client_id) = explode(":", $ltiMessageHint);
ilSession::set('lti13_login_data', $body);
$url = "../../goto.php?target=lti_" . $ref_id . "&client_id=" . $client_id;
$DIC->http()->saveResponse(
    $DIC->http()->response()
    ->withStatus(302)
    ->withAddedHeader('Location', $url)
);
$DIC->http()->sendResponse();
$DIC->http()->close();
