<?php

/**
 * Class ilWACPath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACPath {

	const DIR_DATA = "data";
	const DIR_SEC = "sec";
	/**
	 * @var string
	 */
	protected $client = '';
	/**
	 * @var string
	 */
	protected $secure_path_id = '';
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
	 * @var string
	 */
	protected $original_request = '';
	/**
	 * @var string
	 */
	protected $path_without_query = '';
	/**
	 * @var bool
	 */
	protected $in_sec_folder = false;
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
	 * @var array
	 */
	protected static $video_suffixes = array(
		'mp4',
		'm4v',
		'mov',
		'wmv',
		'webm',
	);
	/**
	 * @var array
	 */
	protected static $audio_suffixes = array(
		'mp3',
		'aiff',
		'aif',
		'wav',
	);


	/**
	 * ilWACPath constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path) {
		$this->setOriginalRequest($path);

		$regex_client = "[\\w-\\.]*";

		preg_match("/\\/" . self::DIR_DATA . "\\/({$regex_client})\\/(" . self::DIR_SEC . "\\/|)([\\w]*)\\/(.*)/ui", $path, $results);
		preg_match("/(\\/" . self::DIR_DATA . "\\/{$regex_client}\\/[\\w]*\\/.*)\\?/ui", $path, $results2);
		$this->setPathWithoutQuery(isset($results2[1]) ? '.' . $results2[1] : '.' . $results[0]);
		$this->setPath('.' . $results[0]);
		$this->setClient($results[1]);
		$this->setInSecFolder($results[2] == 'sec/');
		$this->setSecurePathId($results[3]);
		$parts = parse_url($path);
		$this->setFileName(basename($parts['path']));
		$this->setQuery($parts['query']);
		parse_str($parts['query'], $query);
		$this->setParameters($query);
		$this->setSuffix(pathinfo($parts['path'], PATHINFO_EXTENSION));
		preg_match("/\\/" . self::DIR_DATA . "\\/({$regex_client})\\/(" . self::DIR_SEC . "\\/[\\w]*\\/[\\d]*\\/|[\\w]*\\/)([\\w]*)\\//ui", $path, $results3);
		$this->setSecurePath(isset($results3[0]) ? '.' . $results3[0] : NULL);
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
	public function isVideo() {
		return in_array(strtolower($this->getSuffix()), self::$video_suffixes);
	}


	/**
	 * @return bool
	 */
	public function isAudio() {
		return in_array(strtolower($this->getSuffix()), self::$audio_suffixes);
	}


	/**
	 * @return bool
	 */
	public function isStreamable() {
		return ($this->isAudio() || $this->isVideo());
	}


	/**
	 * @return bool
	 */
	public function fileExists() {
		return is_file($this->getPathWithoutQuery());
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
	 * @param $token
	 */
	public function setToken($token) {
		$param = $this->getParameters();
		$param[ilWACSignedPath::WAC_TOKEN_ID] = $token;
		$this->setParameters($param);
	}


	/**
	 * @return int
	 */
	public function getTimestamp() {
		$param = $this->getParameters();

		return (int)($param[ilWACSignedPath::WAC_TIMESTAMP_ID]);
	}


	/**
	 * @param $timestamp
	 */
	public function setTimestamp($timestamp) {
		$param = $this->getParameters();
		$param[ilWACSignedPath::WAC_TIMESTAMP_ID] = $timestamp;
		$this->setParameters($param);
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
	public function getSecurePathId() {
		return $this->secure_path_id;
	}


	/**
	 * @param string $secure_path_id
	 */
	public function setSecurePathId($secure_path_id) {
		$this->secure_path_id = $secure_path_id;
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


	/**
	 * @return string
	 */
	public function getOriginalRequest() {
		return $this->original_request;
	}


	/**
	 * @param string $original_request
	 */
	public function setOriginalRequest($original_request) {
		$this->original_request = $original_request;
	}


	/**
	 * @return array
	 */
	public static function getImageSuffixes() {
		return self::$image_suffixes;
	}


	/**
	 * @param array $image_suffixes
	 */
	public static function setImageSuffixes($image_suffixes) {
		self::$image_suffixes = $image_suffixes;
	}


	/**
	 * @return array
	 */
	public static function getVideoSuffixes() {
		return self::$video_suffixes;
	}


	/**
	 * @param array $video_suffixes
	 */
	public static function setVideoSuffixes($video_suffixes) {
		self::$video_suffixes = $video_suffixes;
	}


	/**
	 * @return string
	 */
	public function getPathWithoutQuery() {
		return $this->path_without_query;
	}


	/**
	 * @param string $path_without_query
	 */
	public function setPathWithoutQuery($path_without_query) {
		$this->path_without_query = $path_without_query;
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
	 * @return boolean
	 */
	public function isInSecFolder() {
		return $this->in_sec_folder;
	}


	/**
	 * @param boolean $in_sec_folder
	 */
	public function setInSecFolder($in_sec_folder) {
		$this->in_sec_folder = $in_sec_folder;
	}
}

?>
