<?php
/**
 * Runs the ILIAS WebAccessChecker 2.0
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */

use ILIAS\HTTP\Cookies\CookieFactoryImpl;

chdir('../../');
/** @noRector */
require_once('./libs/composer/vendor/autoload.php');

$container = new \ILIAS\DI\Container();

//manually init http service
$container['http.request_factory'] = static fn ($c) => new \ILIAS\HTTP\Request\RequestFactoryImpl();

$container['http.response_factory'] = static fn ($c) => new \ILIAS\HTTP\Response\ResponseFactoryImpl();

$container['http.cookie_jar_factory'] = static fn ($c) => new \ILIAS\HTTP\Cookies\CookieJarFactoryImpl();

$container['http.response_sender_strategy'] = static fn ($c) => new \ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy();

$container['http.duration_factory'] = static fn ($c) => new \ILIAS\HTTP\Duration\DurationFactory(
    new \ILIAS\HTTP\Duration\Increment\IncrementFactory()
);

$container['http'] = static fn ($c) => new \ILIAS\HTTP\Services($c);

$GLOBALS["DIC"] = $container;

/**
 * @var \ILIAS\HTTP\Services $Services
 */
$Services = $container['http'];

//TODO: fix tests and mod_xsendfile which refuses to work
ilWebAccessCheckerDelivery::run($Services, new CookieFactoryImpl());

//send response
$Services->sendResponse();
