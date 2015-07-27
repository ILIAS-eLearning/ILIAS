<?php

/**
 * Class ilWACPath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACPath {

	/**
	 * @var string
	 */
	protected $client = '';
	/**
	 * @var string
	 */
	protected $secure_path = '';
	/**
	 * @var string
	 */
	protected $path = '';
	/**
	 * @var string
	 */
	protected $suffix = '';
	/**
	 * @var string
	 */
	protected $query = '';
	/**
	 * @var array
	 */
	protected $parameters = array();
	/**
	 * @var string
	 */
	protected $file_name = '';
	/**
	 * @var array
	 */
	protected static $image_suffixes = array(
		'png',
		'jpg',
		'jpeg',
		'gif',
		'svg',
	);


	/**
	 * ilWACPath constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path) {
		preg_match("/\\/data\\/([a-zA-Z0-9_]*)\\/([a-zA-Z0-9_]*)\\/(.*)/ui", $path, $results);
//		echo '<pre>' . print_r($results, 1) . '</pre>';
//		exit;
		$this->setPath('.' . $results[0]);
		$this->setClient($results[1]);
		$this->setSecurePath($results[2]);
		$parts = parse_url($path);
		$this->setFileName(basename($parts['path']));
		$this->setQuery($parts['query']);
		parse_str($parts['query'], $query);
		$this->setParameters($query);
		$this->setSuffix(pathinfo($parts['path'], PATHINFO_EXTENSION));
	}


	/**
	 * @return bool
	 */
	public function isImage() {
		return in_array(strtolower($this->getSuffix()), self::$image_suffixes);
	}


	/**
	 * @return bool
	 */
	public function hasToken() {
		$param = $this->getParameters();

		return isset($param[ilWACSignedPath::WAC_TOKEN_ID]);
	}


	/**
	 * @return bool
	 */
	public function hasTimestamp() {
		$param = $this->getParameters();

		return isset($param[ilWACSignedPath::WAC_TIMESTAMP_ID]);
	}


	/**
	 * @return string
	 */
	public function getToken() {
		$param = $this->getParameters();

		return ($param[ilWACSignedPath::WAC_TOKEN_ID]);
	}


	/**
	 * @return int
	 */
	public function getTimestamp() {
		$param = $this->getParameters();

		return ($param[ilWACSignedPath::WAC_TIMESTAMP_ID]);
	}


	/**
	 * @return string
	 */
	public function getClient() {
		return $this->client;
	}


	/**
	 * @param string $client
	 */
	public function setClient($client) {
		$this->client = $client;
	}


	/**
	 * @return string
	 */
	public function getSecurePath() {
		return $this->secure_path;
	}


	/**
	 * @param string $secure_path
	 */
	public function setSecurePath($secure_path) {
		$this->secure_path = $secure_path;
	}


	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}


	/**
	 * @return string
	 */
	public function getSuffix() {
		return $this->suffix;
	}


	/**
	 * @param string $suffix
	 */
	public function setSuffix($suffix) {
		$this->suffix = $suffix;
	}


	/**
	 * @return string
	 */
	public function getQuery() {
		return $this->query;
	}


	/**
	 * @param string $query
	 */
	public function setQuery($query) {
		$this->query = $query;
	}


	/**
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}


	/**
	 * @param array $parameters
	 */
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}


	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->file_name;
	}


	/**
	 * @param string $file_name
	 */
	public function setFileName($file_name) {
		$this->file_name = $file_name;
	}
}

?>
