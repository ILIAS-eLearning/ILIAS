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

require_once __DIR__ . '/../vendor/composer/vendor/autoload.php';

use ILIAS\DI\Container;
use ILIAS\FileDelivery\Init;
use ILIAS\FileDelivery\Services;

$c = new Container();
Init::init($c);
/** @var Services $file_delivery */
/** @var ILIAS\HTTP\Services $http */
$file_delivery = $c['file_delivery'];
$http = $c['http'];

$requested_url = (string) $http->request()->getUri();

// get everything after StreamDelivery::DELIVERY_ENDPOINT in the requested url
$access_token = substr(
    $requested_url,
    strpos($requested_url, Services::DELIVERY_ENDPOINT) + strlen(Services::DELIVERY_ENDPOINT)
);

$file_delivery->delivery()->deliverFromToken($access_token);
