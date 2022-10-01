<?php

declare(strict_types=1);

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

/**
 * There is no way to process a $_GET Request with
 * a valid third-party client_id param in regular initILIAS
 */
if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $orig = new ArrayObject($_POST);
    $data = $orig->getArrayCopy();
}
elseif (strtoupper($_SERVER['REQUEST_METHOD']) == 'GET') {
    $orig = new ArrayObject($_GET);
    $data = $orig->getArrayCopy();
    // early removing client_id from $_GET
    // otherwise the client_id is interpreted as ILIAS client_id
    // and client.ini.php will not be found
    if (isset($_GET['client_id'])) {
        unset($_GET['client_id']);
    }
}
else {
    header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);
    exit;
}

if (!class_exists('ilInitialisation')) {
    require_once("libs/composer/vendor/autoload.php");
}
ilInitialisation::initILIAS();

global $DIC;

$ltiMessageHint = $data['lti_message_hint'];
if (empty($ltiMessageHint)) {
    $DIC->http()->saveResponse(
        $DIC->http()->response()
        ->withStatus(400)
    );
    $DIC->http()->sendResponse();
    $DIC->http()->close();
}
list($ref_id, $client_id) = explode(":", $ltiMessageHint);
ilSession::set('lti13_login_data', $data);
$url = "../../goto.php?target=lti_" . $ref_id . "&client_id=" . $client_id;
$DIC->http()->saveResponse(
    $DIC->http()->response()
    ->withStatus(302)
    ->withAddedHeader('Location', $url)
);
$DIC->http()->sendResponse();
$DIC->http()->close();
