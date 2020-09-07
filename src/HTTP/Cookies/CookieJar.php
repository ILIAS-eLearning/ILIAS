<?php

namespace ILIAS\HTTP\Cookies;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface CookieJar
 *
 * The cookie jar represents a collection of cookies.
 *
 * The cookie jar never manipulates the response automatically. Therefore please
 * call the renderIntoResponseHeader method, when you are done manipulating the cookies.
 *
 * Please note that all concrete implementations of the jar must be immutable.
 * There is no need to implement custom deep copy mechanism because the cookie itself must
 * be immutable to.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
interface CookieJar
{

    /**
     * Checks if a cookie with the given name is in the jar.
     *
     * @param string $name Cookie name.
     *
     * @return bool         True if the cookie exists otherwise false.
     */
    public function has($name);


    /**
     * Fetches the cookie with the given name from the current jar.
     * If no cookie could be found, null is returned.
     *
     * @param string $name Name of the cookie which should be returned.
     *
     * @return Cookie | null
     */
    public function get($name);


    /**
     * Fetches all cookies from the current jar.
     *
     * @return Cookie[]
     */
    public function getAll();


    /**
     * Creates a new cookie jar with the given cookie.
     *
     * @param Cookie $setCookie The cookie which should be added to the jar.
     *
     * @return CookieJar           New cookie jar which holds the new cookie.
     */
    public function with(Cookie $setCookie);


    /**
     * Creates a cookie jar without the specified cookie.
     *
     * @param string $name Cookie name.
     *
     * @return CookieJar   New cookie jar.
     */
    public function without($name);


    /**
     * Render CookieJar into a Response.
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function renderIntoResponseHeader(ResponseInterface $response);
}
