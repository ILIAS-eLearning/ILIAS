<?php

namespace ILIAS\FileDelivery;

use ILIAS\HTTP\GlobalHttpState;

/**
 * Trait HttpServiceAware
 *
 * This trait provide a convenient way to consume the global http state
 * and aids to reduce code duplication.
 *
 * Please only use this trait if you need the global http state from a
 * static context! Otherwise consider to pass the http global state via constructor (DI).
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @version 1.0
 * @since   5.3
 *
 * @Internal
 */
trait HttpServiceAware
{
    private static $http;


    /**
     * Fetches the global http state from ILIAS.
     *
     * The GlobalHttpStore is stored after the first
     * invocation.
     *
     * @return GlobalHttpState  The current http global state of ILIAS.
     * @since 5.3
     */
    protected static function http()
    {
        if (self::$http === null) {
            self::$http = $GLOBALS['DIC']['http'];
        }

        return self::$http;
    }
}
