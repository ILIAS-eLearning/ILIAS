<?php

chdir("../../");
require_once 'libs/composer/vendor/autoload.php';

if (!isset($_GET['client_id']) || !strlen($_GET['client_id'])) {
    header('HTTP/1.1 401 Authorization Required');
    exit;
}

\LTI\ilLTIConsumerDataService::initIlias($_GET['client_id']);

$dic = $GLOBALS['DIC'];

$log = ilLoggerFactory::getLogger('lti');

$service = new ilLTIConsumerResultService;
$service->handleRequest();
