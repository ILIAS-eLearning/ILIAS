<?php

namespace ILIAS\HTTP\Cookies;

use Dflydev\FigCookies\SetCookie;

/**
 * Class CookieFactoryImpl
 *
 * The cookie factory provides different methods to create cookies.
 *
 * @package ILIAS\HTTP\Cookies
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
class CookieFactoryImpl implements CookieFactory
{

    /**
     * @inheritdoc
     */
    public function create(string $name, string $value = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        return new CookieWrapper(SetCookie::create($name, $value));
    }


    /**
     * @inheritdoc
     */
    public function createRememberedForLongTime(string $name, string $value = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        return new CookieWrapper(SetCookie::createRememberedForever($name, $value));
    }


    /**
     * @inheritdoc
     */
    public function createExpired(string $name) : \ILIAS\HTTP\Cookies\Cookie
    {
        return new CookieWrapper(SetCookie::createExpired($name));
    }


    /**
     * @inheritdoc
     */
    public function fromSetCookieString(string $string) : \ILIAS\HTTP\Cookies\Cookie
    {
        return new CookieWrapper(SetCookie::fromSetCookieString($string));
    }
}