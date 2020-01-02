<?php

namespace ILIAS\HTTP\Cookies;

use Dflydev\FigCookies\SetCookies;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CookieJarFactoryImpl
 *
 * The cookie jar factory provides methods to create cookie jars.
 *
 * @author  Nicolas Schaefli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
class CookieJarFactoryImpl implements CookieJarFactory
{

    /**
     * @inheritdoc
     */
    public function fromCookieStrings($cookieStrings)
    {
        return new CookieJarWrapper(SetCookies::fromSetCookieStrings($cookieStrings));
    }


    /**
     * @inheritdoc
     */
    public function fromResponse(ResponseInterface $response)
    {
        return new CookieJarWrapper(SetCookies::fromResponse($response));
    }
}
