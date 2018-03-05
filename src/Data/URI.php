<?php

namespace ILIAS\Data;


/**
 * The scope of this class is split ilias-conform URI's into components.
 * Please refer to RFC 3986 for details.
 * Notice: ilias-confor URI's will form a SUBSET of RFC 3986.
 * We limit the set of allowed sub-delimiters in authority.
 * We require a schema and an authority to be present.
 * If any part is located and it is invalid an exception will be thrown
 * instead of just omiting it.
 */
class URI
{
	protected $schema;
	protected $authority;
	protected $path;
	protected $query;
	protected $fragment;


	const ALPHA_SUB_REGEX = 'A-Za-z';
	const DIGIT_SUB_REGEX = '0-9';
	const ALPHA_DIGIT_SUB_REGEX = self::ALPHA_SUB_REGEX.self::DIGIT_SUB_REGEX;
	const UNRESERVED_SUB_REGEX = self::ALPHA_DIGIT_SUB_REGEX.'\\-\\._~';
	const PENCODED_SUB_REGEX = self::ALPHA_DIGIT_SUB_REGEX.'%';
	const BASEURI_ALLOWED_SUBDELIMS_SUB_REGEX = '\\$,;';
	const SUBDELIMS_SUB_REGEX = '\\$,;=!&\'"\\(\\)\\*\\+';

	const BASEURI_PCHAR_SUB_REGEX =
							self::PENCODED_SUB_REGEX
							.self::BASEURI_ALLOWED_SUBDELIMS_SUB_REGEX
							.self::UNRESERVED_SUB_REGEX
							.':@';

	const PCHAR_SUB_REGEX = self::PENCODED_SUB_REGEX
							.self::SUBDELIMS_SUB_REGEX
							.self::UNRESERVED_SUB_REGEX
							.':@';

	const SCHEMA_REGEX = '#^['.self::ALPHA_SUB_REGEX.']'
							.'['.self::ALPHA_DIGIT_SUB_REGEX.'\\+\\-\\.]*(?=(://))#';

	const AUTHORITY_REGEX = '#^['.self::UNRESERVED_SUB_REGEX
								.self::PENCODED_SUB_REGEX
								.self::BASEURI_ALLOWED_SUBDELIMS_SUB_REGEX
								.']+(:['.self::DIGIT_SUB_REGEX.']+)?(?=(\\#|\\?|/|$))#';

	const HOST_REGEX = '#^['.self::UNRESERVED_SUB_REGEX
							.self::PENCODED_SUB_REGEX
							.self::BASEURI_ALLOWED_SUBDELIMS_SUB_REGEX.']+(?=(:|\\#|\\?|/|$))#';

	//starts with ':' and ends with $ within authority
	const PORT_LOCATION_REGEX = '#(?<=:).+$#';
	const PORT_REGEX = '#['.self::DIGIT_SUB_REGEX.']+$#';
	
	//starts with end authority and ends with any ?,# or $
	const PATH_LOCATION_REGEX = '#^[^\\#^\\?]+(?=(\\#|\\?|$))#'; 
	const PATH_REGEX = '#^['.self::BASEURI_PCHAR_SUB_REGEX.'/]+$#';
	
	//starts with first ? and ends with any # or $
	const QUERY_LOCATION_REGEX = '#^\\?[^\\#]+(?=(\\#|$))#';
	const QUERY_REGEX = '#^\\?['.self::PCHAR_SUB_REGEX.'/\\?]+$#';

	//starts with first # and ends with $
	const FRAGMENT_LOCATION_REGEX = '#\\#.+$#';
	const FRAGMENT_REGEX = '#^\\#['.self::PCHAR_SUB_REGEX.'/\\?]+$#';


	/**
	 * Walk left-right and try to identify uri-building-parts.
	 */
	public function __construct($uri_string)
	{
		assert('is_string($uri_string)');
		$uri_string = trim($uri_string);
		list($uri_string, $this->schema) = $this->trimBySchema($uri_string);
		list($uri_string, $this->authority, $this->host, $this->port) = $this->trimByAuthority($uri_string);
		list($uri_string, $path) = $this->trimByPath($uri_string);
		$this->path = $path;
		list($uri_string, $query) = $this->trimByQuery($uri_string);
		$this->query = $query;
		list($uri_string, $fragment) = $this->trimByFragment($uri_string);
		$this->fragment = $fragment;
	}

	/**
	 * Find schema in uri_string. Extract it and cut uri_string by the
	 * schema-part.
	 *
	 * @return	[string,string]	leftover_uri,located schema
	 */
	protected function trimBySchema($uri_string)
	{
		$schema = null;
		if(preg_match(self::SCHEMA_REGEX, $uri_string, $finding) === 1) {
			$schema = $finding[0];
			$uri_string = substr($uri_string, strlen($schema) + 3); // remove '://'
		} else {
			throw new \InvalidArgumentException('undefined schema');
		}
		return [$uri_string,$schema];
	}

	/**
	 * Find authority and its subparts in uri_string.
	 * Extract them and cut uri_string by the
	 * authority-part.
	 *
	 * @return	[string,string,string,string]	leftover_uri,located schema,located host,located port
	 */
	protected function trimByAuthority($uri_string)
	{
		$authority = null;
		if(preg_match(self::AUTHORITY_REGEX, $uri_string, $finding) === 1) {
			$authority = $finding[0]; 
			$uri_string = substr($uri_string, strlen($authority));
		} else {
			throw new \InvalidArgumentException('undefined authority');
		}
		$host = null;
		if(preg_match(self::HOST_REGEX, $authority, $finding) === 1) {
			$host = $finding[0];
		}
		$port = null;
		if(preg_match(self::PORT_LOCATION_REGEX, $authority, $finding) === 1) {
			$port = $finding[0];
			if(!preg_match(self::PORT_REGEX, $port)) {
				throw new \InvalidArgumentException('invalid port '.$port);
			}
		}
		$uri_string = ltrim($uri_string,'/'); //remove any number of left-dangling '/' since they are irrelevant
		return [$uri_string,$authority,$host,$port];
	}

	/**
	 * Find path in uri_string. Extract it and cut uri_string by the
	 * path-part.
	 *
	 * @return	[string,string]	leftover_uri,located path
	 */
	protected function trimByPath($uri_string)
	{
		$path = null;
		if(preg_match(self::PATH_LOCATION_REGEX, $uri_string, $finding) === 1) {
			$path = $finding[0]; 
			if(!preg_match(self::PATH_REGEX, $path)) {
				throw new \InvalidArgumentException('invalid path '.$path);
			}
			$uri_string = substr($uri_string, strlen($path));
			$path = trim($path,'/');
		}
		ltrim($uri_string,'/');
		return [$uri_string,$path];
	}

	/**
	 * Find query in uri_string. Extract it and cut uri_string by the
	 * query-part.
	 *
	 * @return	[string,string]	leftover_uri,located query
	 */
	protected function trimByQuery($uri_string)
	{
		$query = null;
		if(preg_match(self::QUERY_LOCATION_REGEX, $uri_string, $finding) === 1) {
			$query = $finding[0];
			if(!preg_match(self::QUERY_REGEX, $query)) {
				throw new \InvalidArgumentException('invalid query '.$query);
			}
			$uri_string = substr($uri_string, strlen($query));
			$query = ltrim($query,'?');
		}
		return [$uri_string,$query];
	}


	/**
	 * Find fragment in uri_string. Extract it and cut uri_string by the
	 * fragment-part.
	 *
	 * @return	[string,string]	leftover_uri,located fragment
	 */
	protected function trimByFragment($uri_string)
	{
		$fragment = null;
		if(preg_match(self::FRAGMENT_LOCATION_REGEX, $uri_string, $finding) === 1) {
			$fragment = $finding[0];
			if(!preg_match(self::FRAGMENT_REGEX, $fragment)) {
				throw new \InvalidArgumentException('invalid fragment '.$fragment);
			}
			$uri_string = substr($uri_string, strlen($fragment));
			$fragment = ltrim($fragment,'#');
		}
		return [$uri_string,$fragment];
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
		return $this->authority;
	}

	/**
	 * @return	string
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
	 * @return	string
	 */
	public function path()
	{
		return $this->path;
	}

	/**
	 * @return	string
	 */
	public function query()
	{
		return $this->query;
	}

	/**
	 * @return	string
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
		$return = $this->schema().'://'.$this->authority();
		if($this->path()) {
			$return .= '/'.$this->path();
		}
		return $return;
	}

}