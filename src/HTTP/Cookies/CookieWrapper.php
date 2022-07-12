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
 * Class CookieWrapper
 * Facade class for the FigCookies SetCookie class.
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.3
 * @version 1.0.0
 */
class CookieWrapper implements Cookie
{
    private SetCookie $cookie;
    
    /**
     * CookieFacade constructor.
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
    public function withValue(string $value = null) : Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withValue($value);
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function withExpires($expires = null) : Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withExpires($expires);
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function rememberForLongTime() : Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->rememberForever();
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function expire() : Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->expire();
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function withMaxAge(int $maxAge = null) : Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withMaxAge($maxAge);
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function withPath(string $path = null) : Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withPath($path);
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function withDomain(string $domain = null) : Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withDomain($domain);
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function withSecure(bool $secure = null) : Cookie
    {
        $clone = clone $this;
        $clone->cookie = $this->cookie->withSecure($secure);
        
        return $clone;
    }
    
    /**
     * @inheritDoc
     */
    public function withHttpOnly(bool $httpOnly = null) : Cookie
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
     * @internal
     */
    public function getImplementation() : SetCookie
    {
        return $this->cookie;
    }
}
