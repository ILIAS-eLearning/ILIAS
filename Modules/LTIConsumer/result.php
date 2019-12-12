<?php

chdir("../../");
require_once 'libs/composer/vendor/autoload.php';

if (!isset($_GET['token']) || !strlen($_GET['token']) || !isset($_GET['client_id']) || !strlen($_GET['client_id'])) {
    header('HTTP/1.1 401 Authorization Required');
    exit;
}

\LTI\ilLTIConsumerDataService::initIlias($_GET['client_id']);

try {
    $token = ilCmiXapiAuthToken::getInstanceByToken($_GET['token']);
    
    $_GET['ref_id'] = $token->getRefId();
} catch (ilCmiXapiException $e) {
    header('HTTP/1.1 401 Authorization Failed');
    exit;
}

$dic = $GLOBALS['DIC'];

$log = ilLoggerFactory::getLogger('lti');

$service = new ilLTIConsumerResultService;
$service->handleRequest($token);
