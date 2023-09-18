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
 */

/**
 * Responsible for loading the UI Framework into the dependency injection container of ILIAS
 */
class InitHttpServices
{
    public function init(\ILIAS\DI\Container $container): void
    {
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

        $container['http.duration_factory'] = function ($c) {
            return new \ILIAS\HTTP\Duration\DurationFactory(
                new \ILIAS\HTTP\Duration\Increment\IncrementFactory()
            );
        };

        $container['http.security'] = function ($c) {
            throw new OutOfBoundsException('TODO');
        };

        $container['http'] = function ($c) {
            return new \ILIAS\HTTP\Services($c);
        };
    }
}
