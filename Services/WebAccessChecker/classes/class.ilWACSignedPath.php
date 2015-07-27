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
	/**
	 * @var string
	 */
	protected $path = '';
	/**
	 * @var ilWACToken
	 */
	protected $token_instance = NULL;
	/**
	 * @var int
	 */
	protected $type = self::TYPE_FILE;


	/**
	 * ilWACSignedPath constructor.
	 *
	 * @param string $path
	 */
	public function __construct($path) {
		$this->setPath(ilWebAccessChecker::normalizePath($path));
	}


	protected function generateTokenInstance() {
		if (! $this->getType()) {
			throw new ilWACException(ilWACException::CODE_NO_TYPE);
		}


		$token = ilWACToken::getInstance($this->getPath());

		$this->setTokenInstance($token);
	}


	/**
	 * @return string
	 */
	public function getSignedPath() {
		if (strpos($this->getPath(), '?')) {
			$path = $this->getPath() . '&' . self::WAC_TOKEN_ID . '=' . $this->getTokenInstance()->getToken();
		} else {
			$path = $this->getPath() . '?' . self::WAC_TOKEN_ID . '=' . $this->getTokenInstance()->getToken();
		}

		return $path . '&' . self::WAC_TIMESTAMP_ID . '=' . $this->getTokenInstance()->getTimestamp();
	}


	/**
	 * @return bool
	 */
	public function isSignedPath() {

		$has_token = (strpos($this->getPath(), self::WAC_TOKEN_ID) !== false);
		$has_timestamp = (strpos($this->getPath(), self::WAC_TIMESTAMP_ID) !== false);

		return ($has_token && $has_timestamp);
	}


	/**
	 * @return bool
	 * @throws ilWACException
	 */
	public function isSignedPathValid() {
		$this->generateTokenInstance();
		$parts = parse_url($this->getPath());
		parse_str($parts['query'], $query);
		$current_timestamp = $this->getTokenInstance()->getTimestamp();
		$timestamp_valid = ($query[self::WAC_TIMESTAMP_ID] > $current_timestamp - 2 && $query[self::WAC_TIMESTAMP_ID] < $current_timestamp + 2);
		$token_valid = ($query[self::WAC_TOKEN_ID] == $this->getTokenInstance()->getToken());

		return ($timestamp_valid && $token_valid);
	}


	/**
	 * @param $path_to_file
	 *
	 * @return string
	 * @throws ilWACException
	 */
	public static function signFile($path_to_file) {
		$obj = new self($path_to_file);
		$obj->setType(self::TYPE_FILE);
		$obj->generateTokenInstance();

		return $obj->getSignedPath();
	}


	/**
	 * @param $folder_path
	 */
	public static function signFolder($folder_path) {
		$obj = new self($folder_path);
		$obj->setType(self::TYPE_FOLDER);
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
}

?>
