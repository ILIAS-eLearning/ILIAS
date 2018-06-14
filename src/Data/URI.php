<?php

namespace ILIAS\Data;


/**
 * The scope of this class is split ilias-conform URI's into components.
 * Please refer to RFC 3986 for details.
 * Notice, ilias-confor URI's will form a SUBSET of RFC 3986:
 *  - Notice the restrictions on baseuri-subdelims.
 *  - We require a schema and an authority to be present.
 *  - If any part is located and it is invalid an exception will be thrown
 *    instead of just omiting it.
 *	- IPv6 is currently not supported.
 */
class URI
{
	/**
	 * @var	string
	 */
	protected $schema;
	/**
	 * @var	string
	 */
	protected $host;
	/**
	 * @var	int|null
	 */
	protected $port;
	/**
	 * @var	string|null
	 */
	protected $path;
	/**
	 * @var	string|null
	 */
	protected $query;
	/**
	 * @var	string|null
	 */
	protected $fragment;

	const PATH_DELIM = '/';

	/**
	 * Relevant character-groups as defined in RFC 3986 Appendix 1
	 */
	const ALPHA = '[A-Za-z]';
	const DIGIT = '[0-9]';
	const ALPHA_DIGIT = '[A-Za-z0-9]';
	const HEXDIG = '[0-9A-F]';
	const PCTENCODED = '%'.self::HEXDIG.self::HEXDIG;
	/**
	 * point|minus|plus to be used in schema.
	 */
	const PIMP = '[\\+\\-\\.]';

	/**
	 * valid subdelims according to RFC 3986 Appendix 1:
	 * "!" "$" "&" "'" "(" ")" "*" "+" "," ";" "="
	 */
	const SUBDELIMS = '[\\$,;=!&\'\\(\\)\\*\\+]';
	/**
	 * subdelims without jsf**k characters +!() and =
	 */
	const BASEURI_SUBDELIMS = '[\\$,;&\'\\*]';

	const UNRESERVED = self::ALPHA_DIGIT.'|[\\-\\._~]';
	const UNRESERVED_NO_DOT = self::ALPHA_DIGIT.'|[\\-_~]';

	const PCHAR = self::UNRESERVED.'|'.self::SUBDELIMS.'|'.self::PCTENCODED.'|:|@';
	const BASEURI_PCHAR = self::UNRESERVED.'|'.self::BASEURI_SUBDELIMS.'|'.self::PCTENCODED.'|:|@';

	const SCHEMA = '#^'.self::ALPHA.'('.self::ALPHA_DIGIT.'|'.self::PIMP.')*$#';
	const DOMAIN_LABEL = self::ALPHA_DIGIT.'(('.self::UNRESERVED_NO_DOT.'|'.self::PCTENCODED.'|'.self::BASEURI_SUBDELIMS.')*'.self::ALPHA_DIGIT.')*';
	const HOST_REG_NAME = '^'.self::DOMAIN_LABEL.'(\\.'.self::DOMAIN_LABEL.')*$';
	const HOST_IPV4 = '^('.self::DIGIT.'{1,3})(\\.'.self::DIGIT.'{1,3}){3}$';
	const HOST = '#'.self::HOST_IPV4.'|'.self::HOST_REG_NAME.'#';
	const PORT = '#^'.self::DIGIT.'+$#';
	const PATH = '#^(?!//)(?!:)('.self::PCHAR.'|'.self::PATH_DELIM.')+$#';
	const QUERY = '#^('.self::PCHAR.'|'.self::PATH_DELIM.'|\\?)+$#';
	const FRAGMENT = '#^('.self::PCHAR.'|'.self::PATH_DELIM.'|\\?|\\#)+$#';

	public function __construct($uri_string)
	{
		assert('is_string($uri_string)');
		$this->schema = $this->digestSchema(parse_url($uri_string,PHP_URL_SCHEME));
		$this->host = $this->digestHost(parse_url($uri_string,PHP_URL_HOST));
		$this->port = $this->digestPort(parse_url($uri_string,PHP_URL_PORT));
		$this->path = $this->digestPath(parse_url($uri_string,PHP_URL_PATH));
		$this->query = $this->digestQuery(parse_url($uri_string,PHP_URL_QUERY));
		$this->fragment = $this->digestFragment(parse_url($uri_string,PHP_URL_FRAGMENT));
	}

	/**
	 * Check schema formating. Return it in case of success.
	 *
	 * @param	string	$schema
	 * @throws	\InvalidArgumentException
	 * @return	string
	 */
	protected function digestSchema($schema)
	{
		return $this->checkCorrectFormatOrThrow(self::SCHEMA, (string)$schema);
	}

	/**
	 * Check host formating. Return it in case of success.
	 *
	 * @param	string	$host
	 * @throws	\InvalidArgumentException
	 * @return	string
	 */
	protected function digestHost($host)
	{
		return $this->checkCorrectFormatOrThrow(self::HOST, (string)$host);
	}

	/**
	 * Check port formating. Return it in case of success.
	 *
 	 * @param	int	$port
	 * @throws	\InvalidArgumentException
	 * @return	int|null
	 */
	protected function digestPort($port)
	{
		if($port === null) {
			return null;
		}
		if(!is_int($port)) {
			throw new \InvalidArgumentException('ill-formated component '.$port);
		}
		return $port;
	}

	/**
	 * Check path formating. Return it in case of success.
	 *
	 * @param	string	$path
	 * @throws	\InvalidArgumentException
	 * @return	string|null
	 */
	protected function digestPath($path)
	{
		if($path === null) {
			return null;
		}
		assert('is_string($path)');
		$path = trim($this->checkCorrectFormatOrThrow(self::PATH, $path),self::PATH_DELIM);
		if($path === '') {
			$path = null;
		}
		return $path;
	}

	/**
	 * Check query formating. Return it in case of success.
	 *
	 * @param	string	$query
	 * @throws	\InvalidArgumentException
	 * @return	string|null
	 */
	protected function digestQuery($query)
	{
		if($query === null) {
			return null;
		}
		assert('is_string($query)');
		return $this->checkCorrectFormatOrThrow(self::QUERY, $query);
	}

	/**
	 * Check fragment formating. Return it in case of success.
	 *
	 * @param	string	$fragment
	 * @throws	\InvalidArgumentException
	 * @return	string|null
	 */
	protected function digestFragment($fragment)
	{
		if($fragment === null) {
			return null;
		}
		assert('is_string($fragment)');
		return $this->checkCorrectFormatOrThrow(self::FRAGMENT, $fragment);
	}


	/**
	 * Check wether a string fits a regexp. Return it, if so,
	 * throw otherwise.
	 *
	 * @param	string	$regexp
	 * @param	string	$string
	 * @throws	\InvalidArgumentException
	 * @return	string|null
	 */
	protected function checkCorrectFormatOrThrow($regexp,$string)
	{
		if(preg_match($regexp, (string)$string) === 1) {
			return $string;
		}
		throw new \InvalidArgumentException('ill-formated component '.$string);
	}

	/**
	 * @return	string
	 */
	public function schema()
	{
		return $this->schema;
	}

	/**
	 * Get URI with modified schema
	 *
	 * @param	string	$schema
	 * @return	URI
	 */
	public function withSchema($schema)
	{
		assert('is_string($schema)');
		$shema = $this->digestSchema($schema);
		$other = clone $this;
		$other->schema = $schema;
		return $other;
	}


	/**
	 * @return	string
	 */
	public function authority()
	{
		$port = $this->port();
		if($port === null) {
			return $this->host();
		}
		return $this->host().':'.$port;

	}


	/**
	 * Get URI with modified authority
	 *
	 * @param	string	$authority
	 * @return	URI
	 */
	public function withAuthority($authority)
	{
		assert('is_string($authority)');
		$parts = explode(':', $authority);
		if(count($parts) > 2) {
			throw new \InvalidArgumentException('ill-formated component '.$authority);
		}
		$host = $this->digestHost($parts[0]);
		$port = null;
		if(array_key_exists(1, $parts)) {
			$port = (int)$this->checkCorrectFormatOrThrow(self::PORT,(string)$parts[1]);
		}
		$other = clone $this;
		$other->host = $host;
		$other->port = $port;
		return $other;
	}

	/**
	 * @return	int|null
	 */
	public function port()
	{
		return $this->port;
	}

	/**
	 * Get URI with modified port
	 *
	 * @param	int|null	$port
	 * @return	URI
	 */
	public function withPort($port = null)
	{
		assert('is_int($port) || is_null($port)');
		$port = $this->digestPort($port);
		$other = clone $this;
		$other->port = $port;
		return $other;
	}

	/**
	 * @return	string
	 */
	public function host()
	{
		return $this->host;
	}

	/**
	 * Get URI with modified host
	 *
	 * @param	string	$host
	 * @return	URI
	 */
	public function withHost($host)
	{
		assert('is_string($host)');
		$host = $this->digestHost($host);
		$other = clone $this;
		$other->host = $host;
		return $other;
	}


	/**
	 * @return	string|null
	 */
	public function path()
	{
		return $this->path;
	}

	/**
	 * Get URI with modified path
	 *
	 * @param	string|null	$path
	 * @return	URI
	 */
	public function withPath($path = null)
	{
		assert('is_string($path) || is_null($path)');
		$path = $this->digestPath($path);
		$other = clone $this;
		$other->path = $path;
		return $other;
	}

	/**
	 * @return	string|null
	 */
	public function query()
	{
		return $this->query;
	}

	/**
	 * Get URI with modified query
	 *
	 * @param	string|null	$query
	 * @return	URI
	 */
	public function withQuery($query = null)
	{
		assert('is_string($query) || is_null($query)');
		$query = $this->digestQuery($query);
		$other = clone $this;
		$other->query = $query;
		return $other;
	}

	/**
	 * @return	string|null
	 */
	public function fragment()
	{
		return $this->fragment;
	}

	/**
	 * Get URI with modified fragment
	 *
	 * @param	string|null	$fragment
	 * @return	URI
	 */
	public function withFragment($fragment = null)
	{
		assert('is_string($fragment) || is_null($fragment)');
		$fragment = $this->digestFragment($fragment);
		$other = clone $this;
		$other->fragment = $fragment;
		return $other;
	}

	/**
	 * Get a well-formed URI consisting only out of
	 * schema, authority and port.
	 *
	 * @return	string
	 */
	public function baseURI()
	{
		$path = $this->path();
		if($path === null) {
			return $this->schema().'://'.$this->authority();
		}
		return $this->schema().'://'.$this->authority().'/'.$path;
	}

}