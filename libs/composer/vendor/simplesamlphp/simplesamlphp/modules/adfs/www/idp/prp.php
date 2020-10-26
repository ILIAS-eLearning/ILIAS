<?php

/**
 * ADFS PRP IDP protocol support for SimpleSAMLphp.
 *
 * @author Hans Zandbelt, SURFnet bv, <hans.zandbelt@surfnet.nl>
 * @package SimpleSAMLphp
 */

namespace SimpleSAML\Module\adfs;

use SimpleSAML\Configuration;
use SimpleSAML\Session;
use Symfony\Component\HttpFoundation\Request;

$config = Configuration::getInstance();
$session = Session::getSessionFromRequest();
$request = Request::createFromGlobals();

$controller = new AdfsController($config, $session);
$t = $controller->prp($request);
$t->send();
