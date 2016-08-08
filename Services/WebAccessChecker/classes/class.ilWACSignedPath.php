<?php
require_once('./Services/WebAccessChecker/class.ilWACException.php');
require_once('class.ilWACToken.php');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
require_once('./Services/WebAccessChecker/classes/class.ilWACCookie.php');

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
	const WAC_TTL_ID = 'il_wac_ttl';
	const TS_SUFFIX = 'ts';
	const TTL_SUFFIX = 'ttl';
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
	 * @var ilWACCookieInterface
	 */
	protected $cookie = null;


	/**
	 * ilWACSignedPath constructor.
	 *
	 * @param \ilWACPath $ilWACPath
	 * @param \ilWACCookieInterface|null $ilWACCookieInterface
	 */
	public function __construct(ilWACPath $ilWACPath, ilWACCookieInterface $ilWACCookieInterface = null) {
		$this->cookie = ($ilWACCookieInterface ? $ilWACCookieInterface : new ilWACCookie());
		$this->setPathObject($ilWACPath);
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

		$path = $path . '&' . self::WAC_TIMESTAMP_ID . '=' . $this->getTokenInstance()->getTimestamp();

		return $path . '&' . self::WAC_TTL_ID . '=' . $this->getTokenInstance()->getTTL();
	}


	/**
	 * @return bool
	 */
	public function isFolderSigned() {
		$this->buildAndSetTokenInstance();

		$exists = $this->cookie->exists($this->getTokenInstance()->getId());
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
		$this->buildAndSetTokenInstance();

		$ilWACPath = $this->getPathObject();
		$ilWACPath->setToken($this->cookie->get($this->getTokenInstance()->getId()));
		$ilWACPath->setTimestamp($this->cookie->get($this->getTokenInstance()->getId() . self::TS_SUFFIX));
		$ilWACPath->setTTL($this->cookie->get($this->getTokenInstance()->getId() . self::TTL_SUFFIX));
		$this->setPathObject($ilWACPath);

		return $this->checkToken();
	}


	protected function saveFolderToken() {
		$cookie_livetime = self::getCookieMaxLifetimeInSeconds();
		$str = 'save folder token for folder: ' . $this->getPathObject()->getSecurePath() . ', valid for ' . $cookie_livetime . 's';
		ilWACLog::getInstance()->write($str);
		$id = $this->getTokenInstance()->getId();
		$expire = time() + $cookie_livetime;
		$this->cookie->set($id, $this->getTokenInstance()->getToken(), time() + 24 * 3600, '/', null, false, false);
		$this->cookie->set($id . self::TS_SUFFIX, $expire, $expire, '/', null, false, false);
		$this->cookie->set($id . self::TTL_SUFFIX, self::getCookieMaxLifetimeInSeconds(), $expire, '/', null, false, false);
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
		return ($this->getPathObject()->hasToken() && $this->getPathObject()->hasTimestamp() && $this->getPathObject()->hasTTL());
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
		$obj->buildAndSetTokenInstance(time(), self::getTokenMaxLifetimeInSeconds());

		return $obj->getSignedPath();
	}


	/**
	 * @param $start_file_path
	 * @param \ilWACCookieInterface|null $ilWACCookieInterface
	 */
	public static function signFolderOfStartFile($start_file_path, ilWACCookieInterface $ilWACCookieInterface = null) {
		$ilWACPath = new ilWACPath($start_file_path);
		$obj = new self($ilWACPath, $ilWACCookieInterface);
		$obj->setType(self::TYPE_FOLDER);
		$obj->buildAndSetTokenInstance(time(), self::getCookieMaxLifetimeInSeconds());
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
		$token_timestamp = $this->getPathObject()->getTimestamp();
		$current_timestamp = time();
		$life_time = $this->getRelevantLifeTime();

		$timestamp_valid = ($token_timestamp > ($current_timestamp - $life_time));

		if (!$timestamp_valid) {
			ilWACLog::getInstance()->write('cookie no longer valid: TS');

			return false;
		}
		$i = 1;
		$ilWACToken = $this->getTokenInstance();

		do {
			$request_token = $this->getPathObject()->getToken();
			$calculated_token = $ilWACToken->getToken();
			$token_valid = ($request_token == $calculated_token);

			$simulated_time = $token_timestamp + $i;
			$ilWACToken = $this->buildTokenInstance($simulated_time, $this->getPathObject()->getTTL());
			$i ++;
		} while ((!$token_valid && ($simulated_time <= $current_timestamp) && ($i <= $life_time + 1)));

		if (!$token_valid) {
			ilWACLog::getInstance()->write('cookie no longer valid: ID');

			return false;
		}

		return true;
	}


	/**
	 * @param null $timestamp
	 * @return \ilWACToken
	 * @throws \ilWACException
	 */
	protected function buildTokenInstance($timestamp = null, $ttl = null) {
		if (!$this->getType()) {
			throw new ilWACException(ilWACException::CODE_NO_TYPE);
		}
		$path = $this->getPathObject()->getSecurePath();
		$client = $this->getPathObject()->getClient();
		$timestamp = $timestamp ? $timestamp : $this->getPathObject()->getTimestamp();
		$ttl = $ttl ? $ttl : $this->getPathObject()->getTTL();

		return new ilWACToken($path, $client, $timestamp, $ttl);
	}


	/**
	 * @param null $timestamp
	 * @param null $ttl
	 * @throws \ilWACException
	 */
	public function buildAndSetTokenInstance($timestamp = null, $ttl = null) {
		$this->setTokenInstance($this->buildTokenInstance($timestamp, $ttl));
	}


	/**
	 * @throws \ilWACException
	 * @deprecated Use buildTokenInstance instead
	 */
	protected function generateFolderToken() {
		$this->setTokenInstance($this->buildTokenInstance());
	}


	/**
	 * @throws \ilWACException
	 * @deprecated Use buildTokenInstance instead
	 */
	protected function generateTokenInstance() {
		$this->setTokenInstance($this->buildTokenInstance());
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
		$request_ttl = $this->getPathObject()->getTTL();
		if ($request_ttl > 0) {
			return $request_ttl;
		}
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