<?php

namespace ILIAS\HTTP\Cookies;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface CookieJarFactory
 *
 * The cookie jar factory provides methods to create cookie jars.
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
interface CookieJarFactory
{

    /**
     * Create CookieJar from a collection of Cookie header value strings.
     *
     * @param string[] $cookieStrings
     *
     * @return CookieJar
     */
    public function fromCookieStrings($cookieStrings);


    /**
     * Create CookieJar from a Response.
     *
     * @param ResponseInterface $response
     *
     * @return CookieJar
     */
    public function fromResponse(ResponseInterface $response);
}
