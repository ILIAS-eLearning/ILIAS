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

$cookie_path = dirname(str_replace($_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF']));

chdir(__DIR__);
while (!is_file('ilias.ini.php')) {
    chdir('..');
    $cookie_path = dirname($cookie_path);
    if (getcwd() === '/') {
        die('Please ensure ILIAS is installed!');
    }
}

$cookie_path .= preg_match('@/$@', $cookie_path) ? '' : '/';
define('IL_COOKIE_PATH', $cookie_path);

require_once('libs/composer/vendor/autoload.php');

ilContext::init(ilContext::CONTEXT_SAML);
ilInitialisation::initILIAS();

\ILIAS\Saml\Module::run();
