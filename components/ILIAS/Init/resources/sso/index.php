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

if (!file_exists('../../ilias.ini.php')) {
    die('The ILIAS setup is not completed. Please run the setup routine.');
}

require_once '../../vendor/composer/vendor/autoload.php';

chdir('..');

$cookie_path = dirname($_SERVER['PHP_SELF'], 2);

/* if ilias is called directly within the docroot $cookie_path
is set to '/' expecting on servers running under windows..
here it is set to '\'.
in both cases a further '/' won't be appended due to the following regex
*/
$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? '/' : '';

if (isset($_GET['client_id'])) {
    if ($cookie_path === "\\") {
        $cookie_path = '/';
    }

    setcookie('ilClientId', $_GET['client_id'], 0, $cookie_path, '');
    $_COOKIE['ilClientId'] = $_GET['client_id'];
}

define('IL_COOKIE_PATH', $cookie_path);

ilContext::init(ilContext::CONTEXT_APACHE_SSO);

ilInitialisation::initILIAS();

ilStartUpGUI::setForcedCommand('doApacheAuthentication');
$ilCtrl->callBaseClass(ilStartUpGUI::class);
