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

exit; // Copy this script to the publically(!) available ILIAS (root) folder

chdir('../..');

require_once 'vendor/composer/vendor/autoload.php';

global $HTTP_RAW_POST_DATA;

// Initialize the error_reporting level, until it will be overwritte when ILIAS gets initialized
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$server = new ilSoapDummyAuthServer();
$server->start();
