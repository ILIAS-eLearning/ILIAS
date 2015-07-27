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
	const SECONDS = 10;
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
	 */
	public function getSignedPath() {
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
	public function isSignedPath() {
		return ($this->getPathObject()->hasToken() && $this->getPathObject()->hasTimestamp());
	}


	/**
	 * @return bool
	 * @throws ilWACException
	 */
	public function isSignedPathValid() {
		$this->generateTokenInstance();
		$current_timestamp = $this->getTokenInstance()->getTimestamp();
		$timestamp_valid = ($this->getPathObject()->getTimestamp() > $current_timestamp - self::SECONDS
			&& $this->getPathObject()->getTimestamp() < $current_timestamp + self::SECONDS);
		$token_valid = ($this->getPathObject()->getToken() == $this->getTokenInstance()->getToken());

		return ($timestamp_valid && $token_valid);
	}


	/**
	 * @param $path_to_file
	 *
	 * @return string
	 * @throws ilWACException
	 */
	public static function signFile($path_to_file) {
		$ilWACPath = new ilWACPath($path_to_file);
		$obj = new self($ilWACPath);
		$obj->setType(self::TYPE_FILE);
		$obj->generateTokenInstance();

		return $obj->getSignedPath();
	}


	/**
	 * @param $folder_path
	 *
	 * @return string
	 * @throws ilWACException
	 */
	public static function signFolder($folder_path) {
		$ilWACPath = new ilWACPath($folder_path);
		$obj = new self($ilWACPath);
		$obj->setType(self::TYPE_FOLDER);
		$obj->generateTokenInstance();

		return $obj->getSignedPath();
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
}

?>
