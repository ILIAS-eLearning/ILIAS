<?php

namespace ILIAS\HTTP\Cookies;

use Psr\Http\Message\ResponseInterface;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
     */
    public function fromCookieStrings(array $cookieStrings) : CookieJar;


    /**
     * Create CookieJar from a Response.
     *
     *
     */
    public function fromResponse(ResponseInterface $response) : CookieJar;
}
