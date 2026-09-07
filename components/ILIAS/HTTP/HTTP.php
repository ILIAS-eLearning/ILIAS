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

declare(strict_types=1);

namespace ILIAS;

use ILIAS\Component\Component;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Services;
use ILIAS\HTTP\Request\RequestFactory;
use ILIAS\HTTP\Request\RequestFactoryImpl;
use ILIAS\HTTP\Response\ResponseFactoryImpl;
use ILIAS\HTTP\Cookies\CookieJarFactoryImpl;
use ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy;
use ILIAS\HTTP\Duration\DurationFactory;
use ILIAS\HTTP\Duration\Increment\IncrementFactory;
use ILIAS\HTTP\Response\ResponseFactory;
use ILIAS\HTTP\Cookies\CookieJarFactory;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use ILIAS\HTTP\Request\HeaderSettings;
use ILIAS\HTTP\Request\HeaderSettingsFromIni;
use ILIAS\Environment\Configuration\Installation\IliasIni;

class HTTP implements Component
{
    public function init(
        array|\ArrayAccess &$define,
        array|\ArrayAccess &$implement,
        array|\ArrayAccess &$use,
        array|\ArrayAccess &$contribute,
        array|\ArrayAccess &$seek,
        array|\ArrayAccess &$provide,
        array|\ArrayAccess &$pull,
        array|\ArrayAccess &$internal,
    ): void {
        $define[] = RequestFactory::class;
        $define[] = ResponseFactory::class;
        $define[] = CookieJarFactory::class;
        $define[] = GlobalHttpState::class;
        $define[] = HeaderSettings::class;

        $implement[HeaderSettings::class] = static fn() => new HeaderSettingsFromIni(
            $use[IliasIni::class]
        );

        // REQUEST FACTORY
        $implement[RequestFactory::class] = static fn() => new RequestFactoryImpl(
            $use[HeaderSettings::class]
        );

        // RESPONSE FACTORY
        $implement[ResponseFactory::class] = static fn() => new ResponseFactoryImpl();

        // COOKIE JAR FACTORY
        $implement[CookieJarFactory::class] = static fn() => new CookieJarFactoryImpl();

        // RESPONSE SENDER STRATEGY
        $internal[ResponseSenderStrategy::class] = static fn(
        ) => new DefaultResponseSenderStrategy();

        $internal[DurationFactory::class] = static fn() => new DurationFactory(
            new IncrementFactory()
        );

        // GLOBAL HTTP STATE / SERVICE
        $implement[GlobalHttpState::class] = static fn() => new Services(
            $use[RequestFactory::class],
            $use[ResponseFactory::class],
            $use[CookieJarFactory::class],
            $internal[ResponseSenderStrategy::class],
            $internal[DurationFactory::class]
        );
    }
}
