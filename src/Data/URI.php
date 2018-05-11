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
	 * Relevan character-groups as defined in RFC 3986 Appendix 1
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
	const DOMAIN_LABEL = self::ALPHA_DIGIT.'(('.self::UNRESERVED_NO_DOT.'|'.self::PCTENCODED.'|'.self::BASEURI_SUBDELIMS.')+'.self::ALPHA_DIGIT.')*';
	const HOST = '#^'.self::DOMAIN_LABEL.'(\\.'.self::DOMAIN_LABEL.')*$#';
	const PORT = '#^'.self::DIGIT.'+$#';
	const PATH = '#^('.self::PCHAR.'|'.self::PATH_DELIM.')+$#';
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
		if($port === null)
		{
			return null;
		}
		assert('is_int($port)');
		return (int)$this->checkCorrectFormatOrThrow(self::PORT, (string)$port);
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
		if($path === null)
		{
			return null;
		}
		assert('is_string($path)');
		$path = trim($this->checkCorrectFormatOrThrow(self::PATH, $path),self::PATH_DELIM);
		if($path !== '')
		{
			return $path;
		}
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
		if($query === null)
		{
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
		if($fragment === null)
		{
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
		} else {
			throw new \InvalidArgumentException('ill-formated component '.$string);
		}
	}

	/**
	 * @return	string
	 */
	public function schema()
	{
		return $this->schema;
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
	 * @return	string|null
	 */
	public function port()
	{
		return $this->port;
	}

	/**
	 * @return	string
	 */
	public function host()
	{
		return $this->host;
	}

	/**
	 * @return	string|null
	 */
	public function path()
	{
		return $this->path;
	}

	/**
	 * @return	string|null
	 */
	public function query()
	{
		return $this->query;
	}

	/**
	 * @return	string|null
	 */
	public function fragment()
	{
		return $this->fragment;
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