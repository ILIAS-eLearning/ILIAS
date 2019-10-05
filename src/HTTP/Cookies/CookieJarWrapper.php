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
    function __construct(SetCookies $cookies)
    {
        $this->cookies = $cookies;
    }


    /**
     * @inheritDoc
     */
    public function has(string $name) : bool
    {
        return $this->cookies->has($name);
    }


    /**
     * @inheritDoc
     */
    public function get(string $name) : ?\ILIAS\HTTP\Cookies\Cookie
    {
        $cookie = $this->cookies->get($name);

        return (is_null($cookie)) ? null : new CookieWrapper($cookie);
    }


    /**
     * @inheritDoc
     */
    public function getAll() : array
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
    public function with(Cookie $setCookie) : \ILIAS\HTTP\Cookies\CookieJar
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
    public function without(string $name) : \ILIAS\HTTP\Cookies\CookieJar
    {
        $clone = clone $this;
        $clone->cookies = $this->cookies->without($name);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function renderIntoResponseHeader(ResponseInterface $response) : \Psr\Http\Message\ResponseInterface
    {
        $response = $this->cookies->renderIntoSetCookieHeader($response);

        return $response;
    }
}