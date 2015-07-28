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
	const TOKEN_MAX_LIFETIME_IN_SECONDS = 5;
	const COOKIE_SEPERATOR = '$';
	/**
	 * @var ilWACPath
	 */
	protected $path_object = NULL;
	/**
	 * @var ilWACToken
	 */
	protected $token_instance = NULL;
	/**
	 * @var int
	 */
	protected $type = self::TYPE_FILE;


	/**
	 * @param ilWACPath $ilWACPath
	 */
	public function __construct(ilWACPath $ilWACPath) {
		$this->setPathObject($ilWACPath);
	}


	protected function generateTokenInstance() {
		if (! $this->getType()) {
			throw new ilWACException(ilWACException::CODE_NO_TYPE);
		}

		$token = ilWACToken::getInstance($this->getPathObject()->getPath());

		$this->setTokenInstance($token);
	}


	/**
	 * @return string
	 * @throws ilWACException
	 */
	public function getSignedPath() {
		if ($this->getType() !== self::TYPE_FILE) {
			throw new ilWACException(ilWACException::WRONG_PATH_TYPE);
		}
		if (! $this->getPathObject()->getOriginalRequest()) {
			return '';
		}
		if (! $this->getPathObject()->fileExists()) {
			return $this->getPathObject()->getOriginalRequest();
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
	public function hasFolderToken() {
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
		if (! $this->hasFolderToken()) {

			return false;
		}
		$this->generateFolderToken();

		$this->getPathObject()->setToken($_COOKIE[$this->getTokenInstance()->getId()]);
		$this->getPathObject()->setTimestamp($_COOKIE[$this->getTokenInstance()->getId() . '_ts']);

		$return = $this->checkToken();
		if ($return) {
			$this->setType(self::TYPE_FOLDER);
			$this->saveFolderToken();
		}

		return $return;
	}


	public function saveFolderToken() {
		if ($this->getType() !== self::TYPE_FOLDER) {
			throw new ilWACException(ilWACException::WRONG_PATH_TYPE);
		}
		$expires = time() + self::TOKEN_MAX_LIFETIME_IN_SECONDS; // FSX use
		$this->generateFolderToken();
		setcookie($this->getTokenInstance()->getId(), $this->getTokenInstance()->getToken(), 0, '/');
		setcookie($this->getTokenInstance()->getId() . '_ts', $expires, 0, '/');
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
		if (! $path_to_file) {
			return '';
		}
		$ilWACPath = new ilWACPath($path_to_file);
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
		$timestamp_valid = ($this->getPathObject()->getTimestamp() > $this->getTokenInstance()->getTimestamp() - self::TOKEN_MAX_LIFETIME_IN_SECONDS);
		$token_valid = ($this->getPathObject()->getToken() == $this->getTokenInstance()->getToken());

		return ($timestamp_valid && $token_valid);
	}


	protected function generateFolderToken() {
		$this->setTokenInstance(ilWACToken::getInstance($this->getPathObject()->getSecurePath()));
	}
}

?>
