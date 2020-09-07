<?php
/**
 * Runs the ILIAS WebAccessChecker 2.0
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

use ILIAS\HTTP\Cookies\CookieFactoryImpl;

chdir('../../');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessCheckerDelivery.php');

$container = new \ILIAS\DI\Container();

//manually init http service
$container['http.request_factory'] = function ($c) {
    return new \ILIAS\HTTP\Request\RequestFactoryImpl();
};

$container['http.response_factory'] = function ($c) {
    return new \ILIAS\HTTP\Response\ResponseFactoryImpl();
};

$container['http.cookie_jar_factory'] = function ($c) {
    return new \ILIAS\HTTP\Cookies\CookieJarFactoryImpl();
};

$container['http.response_sender_strategy'] = function ($c) {
    return new \ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy();
};

$container['http'] = function ($c) {
    return new \ILIAS\DI\HTTPServices(
        $c['http.response_sender_strategy'],
        $c['http.cookie_jar_factory'],
        $c['http.request_factory'],
        $c['http.response_factory']
    );
};

$GLOBALS["DIC"] = $container;

/**
 * @var \ILIAS\HTTP\GlobalHttpState $globalHttpState
 */
$globalHttpState = $container['http'];

//TODO: fix tests and mod_xsendfile which refuses to work
ilWebAccessCheckerDelivery::run($globalHttpState, new CookieFactoryImpl());

//send response
$globalHttpState->sendResponse();
