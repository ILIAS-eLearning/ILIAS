<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilWACToken
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACToken
{
    private const SALT_FILE_PATH = './data/wacsalt.php';
    protected static string $SALT = '';
    protected string $session_id = '';
    protected int $timestamp = 0;
    protected string $ip = '';
    protected string $token = '';
    protected string $raw_token = '';
    protected string $path = '';
    protected string $id = '';
    protected string $client = '';
    protected int $ttl = 0;


    /**
     * ilWACToken constructor.
     */
    public function __construct(
        string $path,
        string $client,
        int $timestamp = 0,
        int $ttl = 0
    ) {
        $this->setClient($client);
        $this->setPath($path);
        $session_id = session_id();
        $this->setSessionId($session_id ?: '-');
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $this->setIp($_SERVER['REMOTE_ADDR']);
        }
        $this->setTimestamp($timestamp !== 0 ? $timestamp : time());
        $ttl = $ttl !== 0 ? $ttl : ilWACSignedPath::getTokenMaxLifetimeInSeconds();
        $this->setTTL($ttl); //  since we do not know the type at this poit we choose the shorter duration for security reasons
        $this->generateToken();
        $this->setId($this->getPath());
    }


    public function generateToken(): void
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


    protected function initSalt(): void
    {
        if (self::getSALT() !== '' && self::getSALT() !== '0') {
            return;
        }
        $salt = '';
        if (is_file(self::SALT_FILE_PATH)) {
            /** @noRector */
            require self::SALT_FILE_PATH;
            self::setSALT($salt);
        }

        if (strcmp($salt, '') === 0) {
            $this->generateSaltFile();
            $this->initSalt();
        }
    }


    /**
     * @throws ilWACException
     */
    protected function generateSaltFile(): void
    {
        if (is_file(self::SALT_FILE_PATH)) {
            unlink(self::SALT_FILE_PATH);
        }
        $template = file_get_contents('./Services/WebAccessChecker/wacsalt.php.template');
        $random = new \ilRandom();
        $salt = md5(time() * $random->int(1000, 9999) . self::SALT_FILE_PATH);
        self::setSALT($salt);
        $template = str_replace('INSERT_SALT', $salt, $template);
        if (is_writable(dirname(self::SALT_FILE_PATH))) {
            file_put_contents(self::SALT_FILE_PATH, $template);
        } else {
            throw new ilWACException(ilWACException::DATA_DIR_NON_WRITEABLE, self::SALT_FILE_PATH);
        }
    }


    public function getSessionId(): string
    {
        return $this->session_id;
    }


    public function setSessionId(string $session_id): void
    {
        $this->session_id = $session_id;
    }


    public function getTimestamp(): int
    {
        return $this->timestamp;
    }


    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }


    public function getIp(): string
    {
        return $this->ip;
    }


    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }


    public function getToken(): string
    {
        return $this->token;
    }


    public function setToken(string $token): void
    {
        $this->token = $token;
    }


    public function getPath(): string
    {
        return $this->path;
    }


    public function setPath(string $path): void
    {
        $this->path = $path;
    }


    public function getId(): string
    {
        return $this->id;
    }


    public function getHashedId(): string
    {
        return md5($this->id);
    }


    public function setId(string $id): void
    {
        $this->id = $id;
    }


    public static function getSALT(): string
    {
        return self::$SALT;
    }


    public static function setSALT(string $salt): void
    {
        self::$SALT = $salt;
    }


    public function getClient(): string
    {
        return $this->client;
    }


    public function setClient(string $client): void
    {
        $this->client = $client;
    }


    public function getTTL(): int
    {
        return $this->ttl;
    }


    public function setTTL(int $ttl): void
    {
        $this->ttl = $ttl;
    }


    public function getRawToken(): string
    {
        return $this->raw_token;
    }


    public function setRawToken(string $raw_token): void
    {
        $this->raw_token = $raw_token;
    }
}
