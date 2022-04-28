<?php

/**
 * Responsible for loading the UI Framework into the dependency injection container of ILIAS
 */
class InitHttpServices
{
    public function init(\ILIAS\DI\Container $container) : void
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

        $container['http.security'] = function ($c) {
            throw new OutOfBoundsException('TODO');
        };

        $container['http'] = function ($c) {
            return new \ILIAS\HTTP\Services($c);
        };
    }
}
