<?php

namespace ILIAS\HTTP\Cookies;

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
     */
    public function create(string $name, string $value = null): Cookie;


    /**
     * Create a new cookie with the given name and value which expires in 5 years.
     *
     * @param string      $name  The unique cookie name.
     * @param null|string $value Cookie value.
     */
    public function createRememberedForLongTime(string $name, string $value = null): Cookie;


    /**
     * Creates an already expired cookie.
     * This is useful if the cookie should be deleted at the client end.
     *
     * @param string $name Cookie name.
     */
    public function createExpired(string $name): Cookie;


    /**
     * Creates the cookie from the cookie string.
     *
     * @param string $string Cookie string.
     */
    public function fromSetCookieString(string $string): Cookie;
}
