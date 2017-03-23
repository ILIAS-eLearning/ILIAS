<?php

namespace ILIAS\HTTP\Cookies;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface CookieJar
 *
 * Specifies the cookie jar interface.
 * All implementations must be immutable.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.2
 * @version 1.0.0
 */
interface CookieJar {

	/**
	 * Checks if a cookie with the given name is the current jar.
	 *
	 * @param string $name Cookie name.
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
	 * @return ResponseInterface
	 */
	public function renderIntoResponseHeader(ResponseInterface $response);


	/**
	 * Create CookieJar from a collection of Cookie header value strings.
	 *
	 * @param string[] $cookieStrings
	 * @return static
	 */
	public static function fromCookieStrings($cookieStrings);


	/**
	 * Create CookieJar from a Response.
	 *
	 * @param ResponseInterface $response
	 *
	 * @return CookieJar
	 */
	public static function fromResponse(ResponseInterface $response);
}