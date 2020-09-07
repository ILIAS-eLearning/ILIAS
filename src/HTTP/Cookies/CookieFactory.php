<?php

namespace ILIAS\HTTP\Cookies;

/**
 * Interface CookieFactory
 *
 * The cookie factory provides different methods to create cookies.
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
interface CookieFactory
{

    /**
     * Create a new cookie with the given name and value.
     *
     * @param string      $name  The unique cookie name.
     * @param null|string $value Cookie value.
     *
     * @return Cookie
     */
    public function create($name, $value = null);


    /**
     * Create a new cookie with the given name and value which expires in 5 years.
     *
     * @param string      $name  The unique cookie name.
     * @param null|string $value Cookie value.
     *
     * @return Cookie
     */
    public function createRememberedForLongTime($name, $value = null);


    /**
     * Creates an already expired cookie.
     * This is useful if the cookie should be deleted at the client end.
     *
     * @param string $name Cookie name.
     *
     * @return Cookie
     */
    public function createExpired($name);


    /**
     * Creates the cookie from the cookie string.
     *
     * @param string $string Cookie string.
     *
     * @return Cookie
     */
    public function fromSetCookieString($string);
}
