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

/**
 * Runs the ILIAS WebAccessChecker 2.0
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
use ILIAS\DI\Container;
use ILIAS\HTTP\Request\RequestFactoryImpl;
use ILIAS\HTTP\Response\ResponseFactoryImpl;
use ILIAS\HTTP\Cookies\CookieJarFactoryImpl;
use ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy;
use ILIAS\HTTP\Duration\DurationFactory;
use ILIAS\HTTP\Duration\Increment\IncrementFactory;
use ILIAS\HTTP\Services;
use ILIAS\HTTP\Cookies\CookieFactoryImpl;

/** @noRector */
require_once(__DIR__ . '/../vendor/composer/vendor/autoload.php');

$container = new Container();

//manually init http service
$container['http.request_factory'] = static fn($c): RequestFactoryImpl => new RequestFactoryImpl();

$container['http.response_factory'] = static fn($c): ResponseFactoryImpl => new ResponseFactoryImpl();

$container['http.cookie_jar_factory'] = static fn($c): CookieJarFactoryImpl => new CookieJarFactoryImpl();

$container['http.response_sender_strategy'] = static fn($c): DefaultResponseSenderStrategy => new DefaultResponseSenderStrategy();

$container['http.duration_factory'] = static fn($c): DurationFactory => new DurationFactory(
    new IncrementFactory()
);

$container['http'] = static fn($c): Services => new Services($c);

$GLOBALS["DIC"] = $container;

/**
 * @var Services $Services
 */
$Services = $container['http'];

//TODO: fix tests and mod_xsendfile which refuses to work
ilWebAccessCheckerDelivery::run($Services, new CookieFactoryImpl());

//send response
$Services->sendResponse();
