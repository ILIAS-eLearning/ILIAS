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

ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$server = new ILIAS\AuthSOAP\SoapDummyAuthServer();
$server->start();
