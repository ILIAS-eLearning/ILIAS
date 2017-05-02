<?php
declare(strict_types=1);

require_once('class.ilWACSignedPath.php');

/**
 * Class ilWACToken
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACToken {

	const SALT_FILE_PATH = './data/wacsalt.php';

	/**
	 * @var string
	 */
	protected static $SALT = '';
	/**
	 * @var string
	 */
	protected $session_id = '';
	/**
	 * @var int
	 */
	protected $timestamp = 0;
	/**
	 * @var string
	 */
	protected $ip = '';
	/**
	 * @var string
	 */
	protected $token = '';
	/**
	 * @var string
	 */
	protected $raw_token = '';
	/**
	 * @var string
	 */
	protected $path = '';
	/**
	 * @var string
	 */
	protected $id = '';
	/**
	 * @var string
	 */
	protected $client = '';
	/**
	 * @var int
	 */
	protected $ttl = 0;


	/**
	 * ilWACToken constructor.
	 *
	 * @param string     $path
	 * @param string     $client
	 * @param int        $timestamp
	 * @param int        $ttl
	 */
	public function __construct(string $path, string $client, int $timestamp = 0, int $ttl = 0) {
		$this->setClient($client);
		$this->setPath($path);
		$session_id = session_id();
		$this->setSessionId($session_id ? $session_id : '-');
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$this->setIp($_SERVER['REMOTE_ADDR']);
		}
		$this->setTimestamp($timestamp ? $timestamp : time());
		$ttl = $ttl ? $ttl : ilWACSignedPath::getTokenMaxLifetimeInSeconds();
		$this->setTTL($ttl); //  since we do not know the type at this poit we choose the shorter duration for security reasons
		$this->generateToken();
		$this->setId($this->getPath());
	}

	/**
	 * @return void
	 */
	public function generateToken() {
		$this->initSalt();
		$token = implode('-', array( self::getSALT(), $this->getIp(), $this->getClient(), $this->getTimestamp(), $this->getTTL()));
		$this->setRawToken($token);
		$token = sha1($token);
		$this->setToken($token);
	}

	/**
	 * @return void
	 */
	protected function initSalt() {
		if (self::getSALT()) {
			return;
		}
		$salt = '';
		if (is_file(self::SALT_FILE_PATH)) {

			require self::SALT_FILE_PATH;
			self::setSALT($salt);
		}

		if (strcmp($salt, '') === 0) {
			$this->generateSaltFile();
			$this->initSalt();
		}
	}


	/**
	 * @return void
	 * @throws ilWACException
	 */
	protected function generateSaltFile() {
		if (is_file(self::SALT_FILE_PATH)) {
			unlink(self::SALT_FILE_PATH);
		}
		$template = file_get_contents('./Services/WebAccessChecker/wacsalt.php.template');
		$salt = md5(time() * rand(1000, 9999) . self::SALT_FILE_PATH);
		self::setSALT($salt);
		$template = str_replace('INSERT_SALT', $salt, $template);
		if (is_writable(dirname(self::SALT_FILE_PATH))) {
			file_put_contents(self::SALT_FILE_PATH, $template);
		} else {
			throw new ilWACException(ilWACException::DATA_DIR_NON_WRITEABLE, self::SALT_FILE_PATH);
		}
	}


	/**
	 * @return string
	 */
	public function getSessionId() : string {
		return $this->session_id;
	}


	/**
	 * @param string $session_id
	 * @return void
	 */
	public function setSessionId(string $session_id) {
		$this->session_id = $session_id;
	}


	/**
	 * @return int
	 */
	public function getTimestamp() : int {
		return $this->timestamp;
	}


	/**
	 * @param int $timestamp
	 * @return void
	 */
	public function setTimestamp(int $timestamp) {
		$this->timestamp = $timestamp;
	}


	/**
	 * @return string
	 */
	public function getIp() : string {
		return $this->ip;
	}


	/**
	 * @param string $ip
	 * @return void
	 */
	public function setIp(string $ip) {
		$this->ip = $ip;
	}


	/**
	 * @return string
	 */
	public function getToken() : string {
		return $this->token;
	}


	/**
	 * @param string $token
	 * @return void
	 */
	public function setToken(string $token) {
		$this->token = $token;
	}


	/**
	 * @return string
	 */
	public function getPath() : string {
		return $this->path;
	}


	/**
	 * @param string $path
	 * @return void
	 */
	public function setPath(string $path) {
		$this->path = $path;
	}


	/**
	 * @return string
	 */
	public function getId() : string {
		return $this->id;
	}


	/**
	 * @return string
	 */
	public function getHashedId() : string {
		return md5($this->id);
	}


	/**
	 * @param string $id
	 */
	public function setId(string $id) {
		$this->id = $id;
	}


	/**
	 * @return string
	 */
	public static function getSALT() : string {
		return self::$SALT;
	}


	/**
	 * @param string $salt
	 * @return void
	 */
	public static function setSALT(string $salt) {
		self::$SALT = $salt;
	}


	/**
	 * @return string
	 */
	public function getClient() : string {
		return $this->client;
	}


	/**
	 * @param string $client
	 * @return void
	 */
	public function setClient(string $client) {
		$this->client = $client;
	}


	/**
	 * @return int
	 */
	public function getTTL() : int {
		return $this->ttl;
	}


	/**
	 * @param int $ttl
	 * @return void
	 */
	public function setTTL(int $ttl) {
		$this->ttl = $ttl;
	}


	/**
	 * @return string
	 */
	public function getRawToken() : string {
		return $this->raw_token;
	}


	/**
	 * @param string $raw_token
	 * @return void
	 */
	public function setRawToken(string $raw_token) {
		$this->raw_token = $raw_token;
	}
}
