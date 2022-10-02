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
if (!class_exists('ilInitialisation')) {
    require_once("libs/composer/vendor/autoload.php");
}
ilInitialisation::initILIAS();
$clientId = (ilSession::has('lti_dynamic_registration_client_id')) ? (string) ilSession::get('lti_dynamic_registration_client_id') : '';
$response = [];
if (empty($clientId)) {
    $response["providerId"] = 0;
    $response["error"] = "could not find created client_id";
} else {
    try {
        $response["providerId"] = ilLTIConsumeProvider::getProviderIdFromClientId($clientId);
        $response["error"] = "";
    } catch (\ILIAS\Filesystem\Exception\IOException $e) {
        $response["providerId"] = 0;
        $response["error"] = $e->getMessage();
    }
}
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
