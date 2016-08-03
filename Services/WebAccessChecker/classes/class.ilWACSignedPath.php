<?php
require_once('./Services/WebAccessChecker/class.ilWACException.php');
require_once('class.ilWACToken.php');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');

/**
 * Class ilWACSignedPath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACSignedPath {

	const TYPE_FILE = 1;
	const TYPE_FOLDER = 2;
	const WAC_TOKEN_ID = 'il_wac_token';
	const WAC_TIMESTAMP_ID = 'il_wac_ts';
	const TS_SUFFIX = 'ts';
	/**
	 * @var ilWACPath
	 */
	protected $path_object = null;
	/**
	 * @var ilWACToken
	 */
	protected $token_instance = null;
	/**
	 * @var int
	 */
	protected $type = self::TYPE_FILE;
	/**
	 * @var int
	 */
	protected static $token_max_lifetime_in_seconds = 3;
	/**
	 * @var int
	 */
	protected static $cookie_max_lifetime_in_seconds = 300;


	/**
	 * @param ilWACPath $ilWACPath
	 */
	public function __construct(ilWACPath $ilWACPath) {
		$this->setPathObject($ilWACPath);
	}


	protected function generateTokenInstance() {
		if (!$this->getType()) {
			throw new ilWACException(ilWACException::CODE_NO_TYPE);
		}

		$this->setTokenInstance(new ilWACToken($this->getPathObject()->getPath(), $this->getPathObject()->getClient()));
	}


	/**
	 * @return string
	 * @throws ilWACException
	 */
	public function getSignedPath() {
		if ($this->getType() !== self::TYPE_FILE) {
			throw new ilWACException(ilWACException::WRONG_PATH_TYPE);
		}
		if (!$this->getPathObject()->getOriginalRequest()) {
			return '';
		}
		if (!$this->getPathObject()->fileExists()) {
			//			return $this->getPathObject()->getOriginalRequest();
		}

		if (strpos($this->getPathObject()->getPath(), '?')) {
			$path = $this->getPathObject()->getPath() . '&' . self::WAC_TOKEN_ID . '=' . $this->getTokenInstance()->getToken();
		} else {
			$path = $this->getPathObject()->getPath() . '?' . self::WAC_TOKEN_ID . '=' . $this->getTokenInstance()->getToken();
		}

		return $path . '&' . self::WAC_TIMESTAMP_ID . '=' . $this->getTokenInstance()->getTimestamp();
	}


	/**
	 * @return bool
	 */
	public function isFolderSigned() {
		$this->generateFolderToken();

		$exists = isset($_COOKIE[$this->getTokenInstance()->getId()]);
		if ($exists) {
			$this->setType(self::TYPE_FOLDER);
		}

		return $exists;
	}


	/**
	 * @return bool
	 * @throws ilWACException
	 */
	public function isFolderTokenValid() {
		if (!$this->isFolderSigned()) {

			return false;
		}
		$this->generateFolderToken();

		$ilWACPath = $this->getPathObject();
		$ilWACPath->setToken($_COOKIE[$this->getTokenInstance()->getId()]);
		$ilWACPath->setTimestamp($_COOKIE[$this->getTokenInstance()->getId() . self::TS_SUFFIX]);
		$this->setPathObject($ilWACPath);

		return $this->checkToken();
	}


	protected function saveFolderToken() {
		$this->generateFolderToken();
		$cookie_livetime = self::getCookieMaxLifetimeInSeconds();
		$str = 'save folder token for folder: ' . $this->getPathObject()->getSecurePath() . ', valid for ' . $cookie_livetime . 's';
		ilWACLog::getInstance()->write($str);
		$id = $this->getTokenInstance()->getId();
		$expire = time() + $cookie_livetime;
		// $expire = null;
		setcookie($id, $this->getTokenInstance()->getToken(), $expire, '/', null, false, false);
		$ts_value = time() + self::getTokenMaxLifetimeInSeconds();
		setcookie($id . self::TS_SUFFIX, $ts_value, $expire, '/', null, false, false);
	}


	/**
	 * @return bool
	 */
	public function revalidatingFolderToken() {
		if ($this->getType() !== self::TYPE_FOLDER) {
			return false;
		}
		ilWACLog::getInstance()->write('revalidating folder token');
		$this->saveFolderToken();

		return true;
	}


	/**
	 * @return bool
	 */
	public function isSignedPath() {
		return ($this->getPathObject()->hasToken() && $this->getPathObject()->hasTimestamp());
	}


	/**
	 * @return bool
	 * @throws ilWACException
	 */
	public function isSignedPathValid() {
		$this->generateTokenInstance();

		return $this->checkToken();
	}


	/**
	 * @param $path_to_file
	 *
	 * @return string
	 * @throws ilWACException
	 */
	public static function signFile($path_to_file) {
		if (!$path_to_file) {
			return '';
		}
		$ilWACPath = new ilWACPath($path_to_file);
		if (!$ilWACPath->getClient()) {
			return $path_to_file;
		}
		$obj = new self($ilWACPath);
		$obj->setType(self::TYPE_FILE);
		$obj->generateTokenInstance();

		return $obj->getSignedPath();
	}


	/**
	 * @param $start_file_path
	 *
	 * @throws ilWACException
	 */
	public static function signFolderOfStartFile($start_file_path) {
		$ilWACPath = new ilWACPath($start_file_path);
		$obj = new self($ilWACPath);
		$obj->setType(self::TYPE_FOLDER);
		$obj->saveFolderToken();
	}


	/**
	 * @return ilWACToken
	 */
	public function getTokenInstance() {
		return $this->token_instance;
	}


	/**
	 * @param ilWACToken $token_instance
	 */
	public function setTokenInstance(ilWACToken $token_instance) {
		$this->token_instance = $token_instance;
	}


	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @param int $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	/**
	 * @return ilWACPath
	 */
	public function getPathObject() {
		return $this->path_object;
	}


	/**
	 * @param ilWACPath $path_object
	 */
	public function setPathObject($path_object) {
		$this->path_object = $path_object;
	}


	/**
	 * @return bool
	 */
	protected function checkToken() {
		$cookie_timestamp = $this->getPathObject()->getTimestamp();
		$current_timestamp = $this->getTokenInstance()->getTimestamp();
		$life_time = $this->getRelevantLifeTime();

		$timestamp_valid = ($cookie_timestamp > ($current_timestamp - $life_time));

		if (!$timestamp_valid) {
			ilWACLog::getInstance()->write('cookie no longer valid: TS');
		}
		$token_valid = ($this->getPathObject()->getToken() == $this->getTokenInstance()->getToken());
		if (!$token_valid) {
			ilWACLog::getInstance()->write('cookie no longer valid: ID');
		}

		return ($timestamp_valid && $token_valid);
	}
	

	protected function generateFolderToken() {
		$this->setTokenInstance(new ilWACToken($this->getPathObject()->getSecurePath(), $this->getPathObject()->getClient()));
	}


	/**
	 * @return int
	 */
	public static function getTokenMaxLifetimeInSeconds() {
		return self::$token_max_lifetime_in_seconds;
	}


	/**
	 * @param int $token_max_lifetime_in_seconds
	 */
	public static function setTokenMaxLifetimeInSeconds($token_max_lifetime_in_seconds) {
		self::$token_max_lifetime_in_seconds = $token_max_lifetime_in_seconds;
	}


	/**
	 * @return int
	 */
	public static function getCookieMaxLifetimeInSeconds() {
		return self::$cookie_max_lifetime_in_seconds;
	}


	/**
	 * @param int $cookie_max_lifetime_in_seconds
	 */
	public static function setCookieMaxLifetimeInSeconds($cookie_max_lifetime_in_seconds) {
		self::$cookie_max_lifetime_in_seconds = $cookie_max_lifetime_in_seconds;
	}


	/**
	 * @return bool|int
	 */
	protected function getRelevantLifeTime() {
		switch ($this->getType()) {
			case self::TYPE_FOLDER:
				$life_time = self::getCookieMaxLifetimeInSeconds();
				break;
			case self::TYPE_FILE:
				$life_time = self::getTokenMaxLifetimeInSeconds();
				break;
			default:
				$life_time = false;
				break;
		}

		return $life_time;
	}
}

?>
