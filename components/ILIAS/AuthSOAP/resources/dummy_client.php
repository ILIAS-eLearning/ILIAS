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

exit();

require_once __DIR__ . '/../../../../vendor/composer/vendor/autoload.php';

use ILIAS\AuthSOAP\SessionValidationClient;
use ILIAS\AuthSOAP\SessionValidationResult;

$default_server = (static function (): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_dir = dirname($_SERVER['SCRIPT_NAME'] ?? '/auth/soap/example/dummy_client.php');

    return $scheme . '://' . $host . rtrim($script_dir, '/') . '/dummy_server.php';
})();

$server = $_GET['server'] ?? $default_server;
$ext_uid = $_GET['ext_uid'] ?? 'testuser';
$soap_pw = $_GET['soap_pw'] ?? 'testpw';
$new_user_raw = $_GET['new_user'] ?? '';
$new_user = $new_user_raw === '1' || $new_user_raw === 'true';

echo '<form>' .
    'server <input size="80" type="text" name="server" value="' . htmlspecialchars($server, ENT_QUOTES) . '"/>' .
    '<br />ext_uid <input size="50" type="text" name="ext_uid" value="' . htmlspecialchars($ext_uid, ENT_QUOTES) . '"/>' .
    '<br />soap_pw <input size="50" type="text" name="soap_pw" value="' . htmlspecialchars($soap_pw, ENT_QUOTES) . '"/>' .
    '<br />new_user <input size="50" type="text" name="new_user" value="' . htmlspecialchars($new_user_raw, ENT_QUOTES) . '"/> (1 for true, 0 for false)' .
    '<br /><input type="submit" /><br />' .
    '<b>The test server will return true/valid, if ext_uid == soap_pw.</b>' .
    '</form>';

echo '<br /><br />----------------------------------------------<br /><br /> Calling Server...';

$client = new SessionValidationClient(
    $server,
    'http://testuri.org',
    true
);

try {
    $result = $client->validateSession($ext_uid, $soap_pw, $new_user);
    show_result($result, 'isValidSession');
} catch (SoapFault $fault) {
    echo '<h2>SOAP Fault</h2><pre>' . htmlspecialchars($fault->getMessage(), ENT_QUOTES) . '</pre>';
}

echo '<br />End Test';

function show_result(SessionValidationResult $result, string $message): void
{
    echo '<h2>Result ' . htmlspecialchars($message, ENT_QUOTES) . '</h2><pre>';
    print_r($result->validation ?: 'FAILED');
    echo '</pre>';

    if ($result->request !== '' || $result->response !== '') {
        echo '<h3>Request</h3><pre>' . htmlspecialchars(
            str_replace('" ', "\"\n ", str_replace('>', ">\n", $result->request)),
            ENT_QUOTES
        ) . '</pre>';
        echo '<h3>Response</h3><pre>' . htmlspecialchars(
            str_replace('" ', "\"\n ", str_replace('>', ">\n", $result->response)),
            ENT_QUOTES
        ) . '</pre>';
    }
}
