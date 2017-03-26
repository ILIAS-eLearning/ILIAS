<?php

namespace ILIAS\HTTP\Cookies;

use Dflydev\FigCookies\SetCookie;

/**
 * Class CookieFacade
 *
 * Facade class for the FigCookies SetCookie class.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @package ILIAS\HTTP\Cookies
 * @since   5.2
 * @version 1.0.0
 */
class CookieWrapper implements Cookie {

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
		return new self($this->cookie->withValue($value));
	}


	/**
	 * @inheritDoc
	 */
	public function withExpires($expires = null)
	{
		return new self($this->cookie->withExpires($expires));
	}


	/**
	 * @inheritDoc
	 */
	public function rememberForever()
	{
		return new self($this->cookie->rememberForever());
	}


	/**
	 * @inheritDoc
	 */
	public function expire()
	{
		return new self($this->cookie->expire());
	}


	/**
	 * @inheritDoc
	 */
	public function withMaxAge($maxAge = null)
	{
		return new self($this->cookie->withMaxAge($maxAge));
	}


	/**
	 * @inheritDoc
	 */
	public function withPath($path = null)
	{
		return new self($this->cookie->withPath($path));
	}


	/**
	 * @inheritDoc
	 */
	public function withDomain($domain = null)
	{
		return new self($this->cookie->withDomain($domain));
	}


	/**
	 * @inheritDoc
	 */
	public function withSecure($secure = null)
	{
		return new self($this->cookie->withSecure($secure));
	}


	/**
	 * @inheritDoc
	 */
	public function withHttpOnly($httpOnly = null)
	{
		return new self($this->cookie->withHttpOnly($httpOnly));
	}


	/**
	 * @inheritDoc
	 */
	public function __toString()
	{
		return $this->cookie->__toString();
	}


	/**
	 * @inheritDoc
	 */
	public static function create($name, $value = null)
	{
		return new self(SetCookie::create($name, $value));
	}


	/**
	 * @inheritDoc
	 */
	public static function createRememberedForever($name, $value = null)
	{
		return new self(SetCookie::createRememberedForever($name, $value));
	}


	/**
	 * @inheritDoc
	 */
	public static function createExpired($name)
	{
		return new self(SetCookie::createExpired($name));
	}


	/**
	 * @inheritDoc
	 */
	public static function fromSetCookieString($string)
	{
		return new self(SetCookie::fromSetCookieString($string));
	}


	/**
	 * Returns the underlying implementation.
	 * Only for package/service internal use!!!
	 *
	 * @internal
	 * @return SetCookie
	 */
	function getImplementation()
	{
		return $this->cookie;
	}
}