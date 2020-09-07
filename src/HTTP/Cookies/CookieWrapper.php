<?php

namespace ILIAS\HTTP\Cookies;

use Dflydev\FigCookies\SetCookie;

/**
 * Class CookieWrapper
 *
 * Facade class for the FigCookies SetCookie class.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
class CookieWrapper implements Cookie
{

    /**
     * Underlying implementation.
     *
     * @var SetCookie $cookie
     */
    private $cookie;


    /**
     * CookieFacade constructor.
     *
     * @param SetCookie $cookie
     */
    public function __construct(SetCookie $cookie)
    {
        $this->cookie = $cookie;
    }


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->cookie->getName();
    }


    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->cookie->getValue();
    }


    /**
     * @inheritDoc
     */
    public function getExpires()
    {
        return $this->cookie->getExpires();
    }


    /**
     * @inheritDoc
     */
    public function getMaxAge()
    {
        return $this->cookie->getMaxAge();
    }


    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->cookie->getPath();
    }


    /**
     * @inheritDoc
     */
    public function getDomain()
    {
        return $this->cookie->getDomain();
    }


    /**
     * @inheritDoc
     */
    public function getSecure()
    {
        return $this->cookie->getSecure();
    }


    /**
     * @inheritDoc
     */
    public function getHttpOnly()
    {
        return $this->cookie->getHttpOnly();
    }


    /**
     * @inheritDoc
     */
    public function withValue($value = null)
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withValue($value);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withExpires($expires = null)
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withExpires($expires);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function rememberForLongTime()
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->rememberForever();

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function expire()
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->expire();

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withMaxAge($maxAge = null)
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withMaxAge($maxAge);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withPath($path = null)
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withPath($path);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withDomain($domain = null)
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withDomain($domain);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withSecure($secure = null)
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withSecure($secure);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withHttpOnly($httpOnly = null)
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withHttpOnly($httpOnly);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->cookie->__toString();
    }


    /**
     * Returns the underlying implementation.
     * Only for package/service internal use!!!
     *
     * @internal
     * @return SetCookie
     */
    public function getImplementation()
    {
        return $this->cookie;
    }
}
