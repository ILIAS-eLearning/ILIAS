<?php

namespace ILIAS\HTTP\Cookies;

use Dflydev\FigCookies\SetCookie;

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
    public function create(string $name, string $value = null) : Cookie
    {
        return new CookieWrapper(SetCookie::create($name, $value));
    }


    /**
     * @inheritdoc
     */
    public function createRememberedForLongTime(string $name, string $value = null) : Cookie
    {
        return new CookieWrapper(SetCookie::createRememberedForever($name, $value));
    }


    /**
     * @inheritdoc
     */
    public function createExpired(string $name) : Cookie
    {
        return new CookieWrapper(SetCookie::createExpired($name));
    }


    /**
     * @inheritdoc
     */
    public function fromSetCookieString(string $string) : Cookie
    {
        return new CookieWrapper(SetCookie::fromSetCookieString($string));
    }
}
