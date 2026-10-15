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

use ILIAS\DI\Container;
use ILIAS\HTTP\Request\RequestFactoryImpl;
use ILIAS\HTTP\Response\ResponseFactoryImpl;
use ILIAS\HTTP\Cookies\CookieJarFactoryImpl;
use ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy;
use ILIAS\HTTP\Duration\DurationFactory;
use ILIAS\HTTP\Duration\Increment\IncrementFactory;
use ILIAS\HTTP\Services;
use ILIAS\HTTP\Request\HeaderSettingsLegacyProxy;
use ILIAS\HTTP\Request\RequestFactory;
use ILIAS\HTTP\Response\ResponseFactory;
use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use ILIAS\HTTP\GlobalHttpState;

/**
 * @deprecated This class is only used for backport compatibility and will be removed in a future release. For most
 * cases this is done by \ILIAS\HTTP::init already. This is needed as long as some other components still rely on old
 * ways of getting the HTTP-service.
 */
class InitHttpServices
{
    /**
     * @deprecated
     */
    public function init(Container $container): void
    {
        $container[RequestFactory::class] = (static fn(Container $c): RequestFactoryImpl => new RequestFactoryImpl(
            new HeaderSettingsLegacyProxy(),
        ));

        $container[ResponseFactory::class] = static fn($c): ResponseFactoryImpl => new ResponseFactoryImpl();

        $container[CookieJarFactory::class] = static fn($c): CookieJarFactoryImpl => new CookieJarFactoryImpl();

        $container[ResponseSenderStrategy::class] = static fn(
            $c
        ): DefaultResponseSenderStrategy => new DefaultResponseSenderStrategy();

        $container[DurationFactory::class] = static fn($c): DurationFactory => new DurationFactory(
            new IncrementFactory()
        );

        $container['http.security'] = static function ($c): void {
            throw new OutOfBoundsException('TODO');
        };

        $container[GlobalHttpState::class] = static fn($c): Services => new Services(
            $c[RequestFactory::class],
            $c[ResponseFactory::class],
            $c[CookieJarFactory::class],
            $c[ResponseSenderStrategy::class],
            $c[DurationFactory::class],
        );
        $container['http'] = static fn($c): Services => $c[GlobalHttpState::class];
    }
}
