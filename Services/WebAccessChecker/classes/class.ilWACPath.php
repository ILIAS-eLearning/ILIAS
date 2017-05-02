<?php
declare(strict_types=1);

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
	 * Copy this without to regex101.com and test with some URL of files
	 */
	const REGEX = "(?<prefix>.*?)(?<path>(?<path_without_query>(?<secure_path_id>(?<module_path>\/data\/(?<client>[\w-\.]*)\/(?<sec>sec\/|)(?<module_type>.*?)\/(?<module_identifier>.*\/|)))(?<appendix>[^\?\n]*)).*)";
	/**
	 * @var string[]
	 */
	protected static $image_suffixes = array(
		'png',
		'jpg',
		'jpeg',
		'gif',
		'svg',
	);
	/**
	 * @var string[]
	 */
	protected static $video_suffixes = array(
		'mp4',
		'm4v',
		'mov',
		'wmv',
		'webm',
	);
	/**
	 * @var string[]
	 */
	protected static $audio_suffixes = array(
		'mp3',
		'aiff',
		'aif',
		'wav',
	);
	/**
	 * @var string
	 */
	protected $client = '';
	/**
	 * @var array
	 */
	protected $parameters = array();
	/**
	 * @var bool
	 */
	protected $in_sec_folder = false;
	/**
	 * @var string
	 */
	protected $token = '';
	/**
	 * @var int
	 */
	protected $timestamp = 0;
	/**
	 * @var int
	 */
	protected $ttl = 0;
	/**
	 * @var string
	 */
	protected $secure_path = '';
	/**
	 * @var string
	 */
	protected $secure_path_id = '';
	/**
	 * @var string
	 */
	protected $original_request = '';
	/**
	 * @var string
	 */
	protected $file_name = '';
	/**
	 * @var string
	 */
	protected $query = '';
	/**
	 * @var string
	 */
	protected $suffix = '';
	/**
	 * @var string
	 */
	protected $prefix = '';
	/**
	 * @var string
	 */
	protected $appendix = '';
	/**
	 * @var string
	 */
	protected $module_path = '';
	/**
	 * @var string
	 */
	protected $path = '';
	/**
	 * @var string
	 */
	protected $module_type = '';
	/**
	 * @var string
	 */
	protected $module_identifier = '';
	/**
	 * @var string
	 */
	protected $path_without_query = '';


	/**
	 * ilWACPath constructor.
	 *
	 * @param string                $path
	 */
	public function __construct(string $path) {
		$this->setOriginalRequest($path);
		$re = '/' . self::REGEX . '/';
		preg_match($re, $path, $result);

		foreach ($result as $k => $v) {
			if (is_numeric($k)) {
				unset($result[$k]);
			}
		}

		$moduleId = strstr($result['module_identifier'] ?? '', "/", true);
		$moduleId = $moduleId === false ? '' : $moduleId;

		$this->setPrefix($result['prefix'] ?? '');
		$this->setClient($result['client'] ?? '');
		$this->setAppendix($result['appendix'] ?? '');
		$this->setModuleIdentifier($moduleId);
		$this->setModuleType($result['module_type'] ?? '');

		$modulePath = NULL;

		if ($this->getModuleIdentifier()) {
			$modulePath = strstr($result['module_path'] ?? '', $this->getModuleIdentifier(), true);
			$modulePath = '.' . ($modulePath === false ? '' : $modulePath);
		}else {
			$modulePath = ('.' . ($result['module_path'] ?? ''));
		}

		$this->setModulePath("$modulePath");
		$this->setInSecFolder($result['sec'] === 'sec/');
		$this->setPathWithoutQuery('.' . ($result['path_without_query'] ?? ''));
		$this->setPath('.' . ($result['path'] ?? ''));
		$this->setSecurePath('.' . ($result['secure_path_id'] ?? ''));
		$this->setSecurePathId($result['module_type'] ?? '');
		// Pathinfo
		$parts = parse_url($path);
		$this->setFileName(basename($parts['path']));
		if (isset($parts['query'])) {
			$parts_query = $parts['query'];
			$this->setQuery($parts_query);
			parse_str($parts_query, $query);
			$this->setParameters($query);
		}
		$this->setSuffix(pathinfo($parts['path'], PATHINFO_EXTENSION));
		$this->handleParameters();
	}


	protected function handleParameters() {
		$param = $this->getParameters();
		if (isset($param[ilWACSignedPath::WAC_TOKEN_ID])) {
			$this->setToken($param[ilWACSignedPath::WAC_TOKEN_ID]);
		}
		if (isset($param[ilWACSignedPath::WAC_TIMESTAMP_ID])) {
			$this->setTimestamp(intval($param[ilWACSignedPath::WAC_TIMESTAMP_ID]));
		}
		if (isset($param[ilWACSignedPath::WAC_TTL_ID])) {
			$this->setTTL(intval($param[ilWACSignedPath::WAC_TTL_ID]));
		}
	}


	/**
	 * @return array
	 */
	public function getParameters() : array {
		return $this->parameters;
	}


	/**
	 * @param array $parameters
	 */
	public function setParameters(array $parameters) {
		$this->parameters = $parameters;
	}


	/**
	 * @return array
	 */
	public static function getAudioSuffixes() : array {
		return self::$audio_suffixes;
	}


	/**
	 * @param array $audio_suffixes
	 */
	public static function setAudioSuffixes(array $audio_suffixes) {
		self::$audio_suffixes = $audio_suffixes;
	}


	/**
	 * @return array
	 */
	public static function getImageSuffixes() : array {
		return self::$image_suffixes;
	}


	/**
	 * @param array $image_suffixes
	 */
	public static function setImageSuffixes(array $image_suffixes) {
		self::$image_suffixes = $image_suffixes;
	}


	/**
	 * @return array
	 */
	public static function getVideoSuffixes() : array {
		return self::$video_suffixes;
	}


	/**
	 * @param array $video_suffixes
	 */
	public static function setVideoSuffixes(array $video_suffixes) {
		self::$video_suffixes = $video_suffixes;
	}


	/**
	 * @return string
	 */
	public function getPrefix() : string {
		return $this->prefix;
	}


	/**
	 * @param string $prefix
	 */
	public function setPrefix(string $prefix) {
		$this->prefix = $prefix;
	}


	/**
	 * @return string
	 */
	public function getAppendix() : string {
		return $this->appendix;
	}


	/**
	 * @param string $appendix
	 */
	public function setAppendix(string $appendix) {
		$this->appendix = $appendix;
	}


	/**
	 * @return string
	 */
	public function getModulePath() : string {
		return $this->module_path;
	}


	/**
	 * @param string $module_path
	 */
	public function setModulePath(string $module_path) {
		$this->module_path = $module_path;
	}


	/**
	 * @return string
	 */
	public function getDirName() : string {
		return dirname($this->getPathWithoutQuery());
	}


	/**
	 * @return string
	 */
	public function getPathWithoutQuery() : string {
		return $this->path_without_query;
	}


	/**
	 * @param string $path_without_query
	 */
	public function setPathWithoutQuery(string $path_without_query) {
		$this->path_without_query = $path_without_query;
	}


	/**
	 * @return bool
	 */
	public function isImage() : bool {
		return in_array(strtolower($this->getSuffix()), self::$image_suffixes);
	}


	/**
	 * @return string
	 */
	public function getSuffix() : string {
		return $this->suffix;
	}


	/**
	 * @param string $suffix
	 */
	public function setSuffix(string $suffix) {
		$this->suffix = $suffix;
	}


	/**
	 * @return bool
	 */
	public function isStreamable() : bool {
		return ($this->isAudio() || $this->isVideo());
	}


	/**
	 * @return bool
	 */
	public function isAudio() : bool {
		return in_array(strtolower($this->getSuffix()), self::$audio_suffixes);
	}


	/**
	 * @return bool
	 */
	public function isVideo() : bool {
		return in_array(strtolower($this->getSuffix()), self::$video_suffixes);
	}


	/**
	 * @return bool
	 */
	public function fileExists() : bool {
		return is_file($this->getPathWithoutQuery());
	}


	/**
	 * @return bool
	 */
	public function hasToken() : bool {
		return ($this->token !== '');
	}


	/**
	 * @return bool
	 */
	public function hasTimestamp() : bool {
		return ($this->timestamp !== 0);
	}


	/**
	 * @return bool
	 */
	public function hasTTL() : bool {
		return ($this->ttl !== 0);
	}


	/**
	 * @return string
	 */
	public function getToken() : string {
		return $this->token;
	}


	/**
	 * @param string $token
	 */
	public function setToken(string $token) {
		$this->parameters[ilWACSignedPath::WAC_TOKEN_ID] = $token;
		$this->token = $token;
	}


	/**
	 * @return int
	 */
	public function getTimestamp() : int {
		return $this->timestamp;
	}


	/**
	 * @param int $timestamp
	 */
	public function setTimestamp(int $timestamp) {
		$this->parameters[ilWACSignedPath::WAC_TIMESTAMP_ID] = $timestamp;
		$this->timestamp = $timestamp;
	}


	/**
	 * @return int
	 */
	public function getTTL() : int {
		return $this->ttl;
	}


	/**
	 * @param int $ttl
	 */
	public function setTTL(int $ttl) {
		$this->parameters[ilWACSignedPath::WAC_TTL_ID] = $ttl;
		$this->ttl = $ttl;
	}


	/**
	 * @return string
	 */
	public function getClient() : string {
		return $this->client;
	}


	/**
	 * @param string $client
	 */
	public function setClient(string $client) {
		$this->client = $client;
	}


	/**
	 * @return string
	 */
	public function getSecurePathId() : string {
		return $this->secure_path_id;
	}


	/**
	 * @param string $secure_path_id
	 */
	public function setSecurePathId(string $secure_path_id) {
		$this->secure_path_id = $secure_path_id;
	}


	/**
	 * @return string
	 */
	public function getPath() : string {
		return $this->path;
	}


	/**
	 * @param string $path
	 */
	public function setPath(string $path) {
		$this->path = $path;
	}


	/**
	 * @return string
	 */
	public function getQuery() : string {
		return $this->query;
	}


	/**
	 * @param string $query
	 */
	public function setQuery(string $query) {
		$this->query = $query;
	}


	/**
	 * @return string
	 */
	public function getFileName() : string {
		return $this->file_name;
	}


	/**
	 * @param string $file_name
	 */
	public function setFileName(string $file_name) {
		$this->file_name = $file_name;
	}


	/**
	 * @return string
	 */
	public function getOriginalRequest() : string {
		return $this->original_request;
	}


	/**
	 * @param string $original_request
	 */
	public function setOriginalRequest(string $original_request) {
		$this->original_request = $original_request;
	}


	/**
	 * @return string
	 */
	public function getSecurePath() : string {
		return $this->secure_path;
	}


	/**
	 * @param string $secure_path
	 */
	public function setSecurePath(string $secure_path) {
		$this->secure_path = $secure_path;
	}


	/**
	 * @return bool
	 */
	public function isInSecFolder() : bool {
		return $this->in_sec_folder;
	}


	/**
	 * @param bool $in_sec_folder
	 */
	public function setInSecFolder(bool $in_sec_folder) {
		$this->in_sec_folder = $in_sec_folder;
	}


	/**
	 * @return string
	 */
	public function getModuleType() : string {
		return $this->module_type;
	}


	/**
	 * @param string $module_type
	 */
	public function setModuleType(string $module_type) {
		$this->module_type = $module_type;
	}


	/**
	 * @return string
	 */
	public function getModuleIdentifier() : string {
		return $this->module_identifier;
	}


	/**
	 * @param string $module_identifier
	 */
	public function setModuleIdentifier(string $module_identifier) {
		$this->module_identifier = $module_identifier;
	}
}