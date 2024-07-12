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

/**
 * Responsible for loading the HTTP Service into the dependency injection container of ILIAS
 */
class InitHttpServices
{
    public function init(Container $container): void
    {
        $container['http.request_factory'] = static function (Container $c): RequestFactoryImpl {
            $header = null;
            $value = null;

            if (
                isset($c['ilIliasIniFile'])
                && (bool) $c->iliasIni()->readVariable('https', 'auto_https_detect_enabled')
            ) {
                $header = (string) $c->iliasIni()->readVariable('https', 'auto_https_detect_header_name');
                $value = (string) $c->iliasIni()->readVariable('https', 'auto_https_detect_header_value');
                $header = $header === '' ? null : $header;
                $value = $value === '' ? null : $value;
            }

            return new RequestFactoryImpl($header, $value);
        };

        $container['http.response_factory'] = static fn($c): ResponseFactoryImpl => new ResponseFactoryImpl();

        $container['http.cookie_jar_factory'] = static fn($c): CookieJarFactoryImpl => new CookieJarFactoryImpl();

        $container['http.response_sender_strategy'] = static fn(
            $c
        ): DefaultResponseSenderStrategy => new DefaultResponseSenderStrategy();

        $container['http.duration_factory'] = static fn($c): DurationFactory => new DurationFactory(
            new IncrementFactory()
        );

        $container['http.security'] = static function ($c): void {
            throw new OutOfBoundsException('TODO');
        };

        $container['http'] = static fn($c): Services => new Services($c);
    }
}
