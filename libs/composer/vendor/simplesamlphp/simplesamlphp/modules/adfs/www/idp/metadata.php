<?php

namespace SimpleSAML\Module\adfs;

use SimpleSAML\Configuration;
use SimpleSAML\Session;
use Symfony\Component\HttpFoundation\Request;

$config = Configuration::getInstance();
$session = Session::getSessionFromRequest();
$request = Request::createFromGlobals();

$controller = new AdfsController($config, $session);
$t = $controller->metadata($request);
$t->send();
