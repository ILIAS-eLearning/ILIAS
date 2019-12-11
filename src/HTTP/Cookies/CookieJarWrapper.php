<?php

namespace ILIAS\HTTP\Cookies;

use Dflydev\FigCookies\SetCookies;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CookieJarWrapper
 *
 * Wrapper class for the FigCookies SetCookies class.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
class CookieJarWrapper implements CookieJar
{

    /**
     * @var SetCookies $cookies
     */
    private $cookies;


    /**
     * CookieJarWrapper constructor.
     *
     * @param SetCookies $cookies
     */
    public function __construct(SetCookies $cookies)
    {
        $this->cookies = $cookies;
    }


    /**
     * @inheritDoc
     */
    public function has($name)
    {
        return $this->cookies->has($name);
    }


    /**
     * @inheritDoc
     */
    public function get($name)
    {
        $cookie = $this->cookies->get($name);

        return (is_null($cookie)) ? null : new CookieWrapper($cookie);
    }


    /**
     * @inheritDoc
     */
    public function getAll()
    {
        $wrappedCookies = [];
        foreach ($this->cookies->getAll() as $cookie) {
            $wrappedCookies[] = new CookieWrapper($cookie);
        }

        return $wrappedCookies;
    }


    /**
     * @inheritDoc
     */
    public function with(Cookie $setCookie)
    {
        /**
         * @var CookieWrapper $wrapper
         */
        $wrapper = $setCookie;
        $internalCookie = $wrapper->getImplementation();

        $clone = clone $this;
        $clone->cookies = $this->cookies->with($internalCookie);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function without($name)
    {
        $clone = clone $this;
        $clone->cookies = $this->cookies->without($name);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function renderIntoResponseHeader(ResponseInterface $response)
    {
        $response = $this->cookies->renderIntoSetCookieHeader($response);

        return $response;
    }
}
