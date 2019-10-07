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
    public function getName() : string
    {
        return $this->cookie->getName();
    }


    /**
     * @inheritDoc
     */
    public function getValue() : ?string
    {
        return $this->cookie->getValue();
    }


    /**
     * @inheritDoc
     */
    public function getExpires() : int
    {
        return $this->cookie->getExpires();
    }


    /**
     * @inheritDoc
     */
    public function getMaxAge() : int
    {
        return $this->cookie->getMaxAge();
    }


    /**
     * @inheritDoc
     */
    public function getPath() : ?string
    {
        return $this->cookie->getPath();
    }


    /**
     * @inheritDoc
     */
    public function getDomain() : ?string
    {
        return $this->cookie->getDomain();
    }


    /**
     * @inheritDoc
     */
    public function getSecure() : bool
    {
        return $this->cookie->getSecure();
    }


    /**
     * @inheritDoc
     */
    public function getHttpOnly() : bool
    {
        return $this->cookie->getHttpOnly();
    }


    /**
     * @inheritDoc
     */
    public function withValue(string $value = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withValue($value);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withExpires($expires = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withExpires($expires);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function rememberForLongTime() : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->rememberForever();

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function expire() : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->expire();

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withMaxAge(int $maxAge = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withMaxAge($maxAge);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withPath(string $path = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withPath($path);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withDomain(string $domain = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withDomain($domain);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withSecure(bool $secure = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withSecure($secure);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withHttpOnly(bool $httpOnly = null) : \ILIAS\HTTP\Cookies\Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withHttpOnly($httpOnly);

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        return $this->cookie->__toString();
    }


    /**
     * Returns the underlying implementation.
     * Only for package/service internal use!!!
     *
     * @return SetCookie
     * @internal
     */
    function getImplementation()
    {
        return $this->cookie;
    }
}