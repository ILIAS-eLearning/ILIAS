<?php
/**
 * Runs the ILIAS WebAccessChecker 2.0
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

chdir('../../');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessCheckerDelivery.php');

$c = new \ILIAS\DI\Container();

//manually init http service
$c["http.response"] = \ILIAS\HTTP\Response\ResponseFactory::create();
$c["http.request"] = \ILIAS\HTTP\Request\RequestFactory::create();

$GLOBALS["DIC"] = $c;

ilWebAccessCheckerDelivery::run($_SERVER['REQUEST_URI']);

//render content
$c->http()->renderResponse();
