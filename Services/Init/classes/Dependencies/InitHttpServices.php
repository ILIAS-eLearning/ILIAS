<?php
/**
 * Responsible for loading the UI Framework into the dependency injection container of ILIAS
 */
class InitHttpServices
{
    public function init(\ILIAS\DI\Container $container){
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
    }

}
