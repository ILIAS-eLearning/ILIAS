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
		return $this->cookie->getMaxAge();
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
		$this->cookie = $this->cookie->withValue($value);

		return new self($this->cookie);
	}


	/**
	 * @inheritDoc
	 */
	public function withExpires($expires = null)
	{
		$this->cookie = $this->cookie->withExpires($expires);

		return new self($this->cookie);
	}


	/**
	 * @inheritDoc
	 */
	public function rememberForever()
	{
		$this->cookie = $this->cookie->rememberForever();

		return new self($this->cookie);
	}


	/**
	 * @inheritDoc
	 */
	public function expire()
	{
		$this->cookie = $this->cookie->expire();

		return new self($this->cookie);
	}


	/**
	 * @inheritDoc
	 */
	public function withMaxAge($maxAge = null)
	{
		$this->cookie = $this->cookie->withMaxAge($maxAge);

		return new self($this->cookie);
	}


	/**
	 * @inheritDoc
	 */
	public function withPath($path = null)
	{
		$this->cookie = $this->cookie->withPath($path);

		return new self($this->cookie);
	}


	/**
	 * @inheritDoc
	 */
	public function withDomain($domain = null)
	{
		$this->cookie = $this->cookie->withDomain($domain);

		return new self($this->cookie);
	}


	/**
	 * @inheritDoc
	 */
	public function withSecure($secure = null)
	{
		$this->cookie = $this->cookie->withSecure($secure);

		return new self($this->cookie);
	}


	/**
	 * @inheritDoc
	 */
	public function withHttpOnly($httpOnly = null)
	{
		$this->cookie = $this->cookie->withHttpOnly($httpOnly);

		return new self($this->cookie);
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