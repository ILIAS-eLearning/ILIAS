<?php
// declare(strict_types=1);

require_once('class.ilWACSignedPath.php');

/**
 * Class ilWACToken
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACToken
{
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
     * @param string $path
     * @param string $client
     * @param int $timestamp
     * @param int $ttl
     */
    public function __construct($path, $client, $timestamp = 0, $ttl = 0)
    {
        assert(is_string($path));
        assert(is_string($client));
        assert(is_int($timestamp));
        assert(is_int($ttl));
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
    public function generateToken()
    {
        $this->initSalt();
        $token = implode('-', array(
            self::getSALT(),
            $this->getClient(),
            $this->getTimestamp(),
            $this->getTTL(),
        ));
        $this->setRawToken($token);
        $token = sha1($token);
        $this->setToken($token);
    }


    /**
     * @return void
     */
    protected function initSalt()
    {
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
    protected function generateSaltFile()
    {
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
    public function getSessionId()
    {
        return (string) $this->session_id;
    }


    /**
     * @param string $session_id
     * @return void
     */
    public function setSessionId($session_id)
    {
        assert(is_string($session_id));
        $this->session_id = $session_id;
    }


    /**
     * @return int
     */
    public function getTimestamp()
    {
        return (int) $this->timestamp;
    }


    /**
     * @param int $timestamp
     * @return void
     */
    public function setTimestamp($timestamp)
    {
        assert(is_int($timestamp));
        $this->timestamp = $timestamp;
    }


    /**
     * @return string
     */
    public function getIp()
    {
        return (string) $this->ip;
    }


    /**
     * @param string $ip
     * @return void
     */
    public function setIp($ip)
    {
        assert(is_string($ip));
        $this->ip = $ip;
    }


    /**
     * @return string
     */
    public function getToken()
    {
        return (string) $this->token;
    }


    /**
     * @param string $token
     * @return void
     */
    public function setToken($token)
    {
        assert(is_string($token));
        $this->token = $token;
    }


    /**
     * @return string
     */
    public function getPath()
    {
        return (string) $this->path;
    }


    /**
     * @param string $path
     * @return void
     */
    public function setPath($path)
    {
        assert(is_string($path));
        $this->path = $path;
    }


    /**
     * @return string
     */
    public function getId()
    {
        return (string) $this->id;
    }


    /**
     * @return string
     */
    public function getHashedId()
    {
        return (string) md5($this->id);
    }


    /**
     * @param string $id
     */
    public function setId($id)
    {
        assert(is_string($id));
        $this->id = $id;
    }


    /**
     * @return string
     */
    public static function getSALT()
    {
        return (string) self::$SALT;
    }


    /**
     * @param string $salt
     * @return void
     */
    public static function setSALT($salt)
    {
        assert(is_string($salt));
        self::$SALT = $salt;
    }


    /**
     * @return string
     */
    public function getClient()
    {
        return (string) $this->client;
    }


    /**
     * @param string $client
     * @return void
     */
    public function setClient($client)
    {
        assert(is_string($client));
        $this->client = $client;
    }


    /**
     * @return int
     */
    public function getTTL()
    {
        return (int) $this->ttl;
    }


    /**
     * @param int $ttl
     * @return void
     */
    public function setTTL($ttl)
    {
        assert(is_int($ttl));
        $this->ttl = $ttl;
    }


    /**
     * @return string
     */
    public function getRawToken()
    {
        return (string) $this->raw_token;
    }


    /**
     * @param string $raw_token
     * @return void
     */
    public function setRawToken($raw_token)
    {
        assert(is_string($raw_token));
        $this->raw_token = $raw_token;
    }
}
