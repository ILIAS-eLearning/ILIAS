<?php
// declare(strict_types=1);

use ILIAS\HTTP\Cookies\CookieFactory;
use ILIAS\HTTP\Cookies\CookieFactoryImpl;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\WebAccessChecker\HttpServiceAware;
use ILIAS\WebAccessChecker\PathType;

require_once('./Services/WebAccessChecker/class.ilWACException.php');
require_once('class.ilWACToken.php');
require_once('./Services/WebAccessChecker/classes/class.ilWebAccessChecker.php');
require_once './Services/WebAccessChecker/interfaces/PathType.php';
require_once './Services/WebAccessChecker/classes/HttpServiceAware.php';

/**
 * Class ilWACSignedPath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACSignedPath
{
    use HttpServiceAware;
    const WAC_TOKEN_ID = 'il_wac_token';
    const WAC_TIMESTAMP_ID = 'il_wac_ts';
    const WAC_TTL_ID = 'il_wac_ttl';
    const TS_SUFFIX = 'ts';
    const TTL_SUFFIX = 'ttl';
    const MAX_LIFETIME = 600;
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
    protected $type = PathType::FILE;
    /**
     * @var int
     */
    protected static $token_max_lifetime_in_seconds = 3;
    /**
     * @var int
     */
    protected static $cookie_max_lifetime_in_seconds = 300;
    /**
     * @var bool
     */
    protected $checked = false;
    /**
     * @var \ILIAS\DI\HTTPServices $httpService
     */
    private $httpService;
    /**
     * @var CookieFactory $cookieFactory
     */
    private $cookieFactory;


    /**
     * ilWACSignedPath constructor.
     *
     * @param \ilWACPath $ilWACPath
     * @param GlobalHttpState $httpState
     * @param CookieFactory $cookieFactory
     */
    public function __construct(ilWACPath $ilWACPath, GlobalHttpState $httpState, CookieFactory $cookieFactory)
    {
        $this->setPathObject($ilWACPath);
        $this->httpService = $httpState;
        $this->cookieFactory = $cookieFactory;
    }


    /**
     * @return string
     * @throws ilWACException
     */
    public function getSignedPath()
    {
        if ($this->getType() !== PathType::FILE) {
            throw new ilWACException(ilWACException::WRONG_PATH_TYPE);
        }
        if (!$this->getPathObject()->getOriginalRequest()) {
            return '';
        }
        if (!$this->getPathObject()->fileExists()) {
            //			return $this->getPathObject()->getOriginalRequest();
        }

        if (strpos($this->getPathObject()->getPath(), '?')) {
            $path = $this->getPathObject()->getPath() . '&' . self::WAC_TOKEN_ID . '='
                    . $this->getTokenInstance()->getToken();
        } else {
            $path = $this->getPathObject()->getPath() . '?' . self::WAC_TOKEN_ID . '='
                    . $this->getTokenInstance()->getToken();
        }

        $path = $path . '&' . self::WAC_TTL_ID . '=' . $this->getTokenInstance()->getTTL();
        $path = $path . '&' . self::WAC_TIMESTAMP_ID . '='
                . $this->getTokenInstance()->getTimestamp();

        return $path;
    }


    /**
     * @return bool
     */
    public function isFolderSigned()
    {
        $jar = $this->httpService->cookieJar();
        $cookies = $jar->getAll();

        $this->setType(PathType::FOLDER);
        $timestamp = time();
        $plain_token = $this->buildTokenInstance($timestamp, self::getCookieMaxLifetimeInSeconds());
        $name = $plain_token->getHashedId();

        // Token
        $default_token = '';
        $token_cookie_value = $this->httpService->request()->getCookieParams()[$name] ?? $default_token;
        // Timestamp
        $default_timestamp = 0;
        $timestamp_cookie_value = $this->httpService->request()->getCookieParams()[$name . self::TS_SUFFIX] ?? $default_timestamp;
        $timestamp_cookie_value = intval($timestamp_cookie_value);
        // TTL
        $default_ttl = 0;
        $ttl_cookie_value = $this->httpService->request()->getCookieParams()[$name . self::TTL_SUFFIX] ?? $default_ttl;
        $ttl_cookie_value = intval($ttl_cookie_value);

        $this->getPathObject()->setToken($token_cookie_value);
        $this->getPathObject()->setTimestamp($timestamp_cookie_value);
        $this->getPathObject()->setTTL($ttl_cookie_value);
        $this->buildAndSetTokenInstance($timestamp, self::getCookieMaxLifetimeInSeconds());

        return $this->getPathObject()->hasToken();
    }


    /**
     * @return bool
     * @throws ilWACException
     */
    public function isFolderTokenValid()
    {
        if (!$this->isFolderSigned()) {
            return false;
        }

        return $this->checkToken();
    }


    /**
     * @return void
     */
    protected function saveFolderToken()
    {
        $cookie_lifetime = self::getCookieMaxLifetimeInSeconds();
        $id = $this->getTokenInstance()->getHashedId();
        $expire = time() + $cookie_lifetime + 3600;
        $secure = true;
        $domain = null;
        $http_only = true;
        $path = '/';

        $tokenCookie = $this->cookieFactory->create($id, $this->getTokenInstance()->getToken())
                                           ->withExpires($expire)
                                           ->withPath($path)
                                           ->withSecure($secure)
                                           ->withDomain($domain)
                                           ->withHttpOnly($http_only);

        $timestampCookie = $this->cookieFactory->create($id . self::TS_SUFFIX, time())
                                               ->withExpires($expire)
                                               ->withPath($path)
                                               ->withDomain($domain)
                                               ->withSecure($secure)
                                               ->withHttpOnly($http_only);

        $ttlCookie = $this->cookieFactory->create($id . self::TTL_SUFFIX, $cookie_lifetime)
                                         ->withExpires($expire)
                                         ->withPath($path)
                                         ->withDomain($domain)
                                         ->withSecure($secure)
                                         ->withHttpOnly($http_only);

        $response = $this->httpService->cookieJar()->with($tokenCookie)
                        ->with($timestampCookie)
                        ->with($ttlCookie)
                        ->renderIntoResponseHeader($this->httpService->response());

        // FIX: currently the cookies are never stored
        foreach ($this->httpService->cookieJar()->getAll() as $cookie) {
           setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpires(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure(), $cookie->getHttpOnly());
        }

        $this->httpService->saveResponse($response);
    }


    /**
     * @return bool
     */
    public function revalidatingFolderToken()
    {
        if ($this->getType() !== PathType::FOLDER) {
            return false;
        }
        $this->buildAndSetTokenInstance(time(), self::getCookieMaxLifetimeInSeconds());
        $this->saveFolderToken();

        return true;
    }


    /**
     * @return bool
     */
    public function isSignedPath()
    {
        return ($this->getPathObject()->hasToken() && $this->getPathObject()->hasTimestamp()
                && $this->getPathObject()->hasTTL());
    }


    /**
     * @return bool
     * @throws ilWACException
     */
    public function isSignedPathValid()
    {
        $this->buildAndSetTokenInstance($this->getPathObject()->getTimestamp(), $this->getPathObject()->getTTL());

        return $this->checkToken();
    }


    /**
     * @param string $path_to_file
     *
     * @return string
     *
     * @throws ilWACException
     */
    public static function signFile($path_to_file)
    {
        if (!$path_to_file) {
            return '';
        }
        $ilWACPath = new ilWACPath($path_to_file);
        if (!$ilWACPath->getClient()) {
            return $path_to_file;
        }
        $obj = new self($ilWACPath, self::http(), new CookieFactoryImpl());
        $obj->setType(PathType::FILE);
        $obj->buildAndSetTokenInstance(time(), self::getTokenMaxLifetimeInSeconds());

        return $obj->getSignedPath();
    }


    /**
     * @param string $start_file_path
     * @return void
     */
    public static function signFolderOfStartFile($start_file_path)
    {
        $obj = new self(new ilWACPath($start_file_path), self::http(), new CookieFactoryImpl());
        $obj->setType(PathType::FOLDER);
        $obj->buildAndSetTokenInstance(time(), self::getCookieMaxLifetimeInSeconds());
        $obj->saveFolderToken();
    }


    /**
     * @return ilWACToken
     */
    public function getTokenInstance()
    {
        return $this->token_instance;
    }


    /**
     * @param ilWACToken $token_instance
     * @return void
     */
    public function setTokenInstance(ilWACToken $token_instance)
    {
        $this->token_instance = $token_instance;
    }


    /**
     * @return int
     */
    public function getType()
    {
        return (int) $this->type;
    }


    /**
     * @param int $type
     * @return void
     */
    public function setType($type)
    {
        assert(is_int($type));
        $this->type = $type;
    }


    /**
     * @return ilWACPath
     */
    public function getPathObject()
    {
        return $this->path_object;
    }


    /**
     * @param ilWACPath $path_object
     * @return void
     */
    public function setPathObject(ilWACPath $path_object)
    {
        $this->path_object = $path_object;
    }


    /**
     * @return bool
     * @throws \ilWACException
     */
    protected function checkToken()
    {
        $request_token = $this->getPathObject()->getToken();
        $request_ttl = $this->getPathObject()->getTTL();
        $request_timestamp = $this->getPathObject()->getTimestamp();
        $current_timestamp = time();

        $timestamp_valid = ($current_timestamp < ($request_timestamp + $request_ttl));

        if (!$timestamp_valid) {
            $this->setChecked(true);

            return false;
        }

        $simulatedTokenInstance = $this->buildTokenInstance($request_timestamp, $request_ttl);
        $token_valid = ($simulatedTokenInstance->getToken() == $request_token);

        if (!$token_valid) {
            $this->setChecked(true);

            return false;
        }

        return true;
    }


    /**
     * @param int $timestamp
     * @param int $ttl
     *
     * @return ilWACToken
     * @throws ilWACException
     */
    protected function buildTokenInstance($timestamp = 0, $ttl = 0)
    {
        assert(is_int($timestamp));
        assert(is_int($ttl));
        if (!$this->getType()) {
            throw new ilWACException(ilWACException::CODE_NO_TYPE);
        }

        switch ($this->getType()) {
            case PathType::FOLDER:
                $path = $this->getPathObject()->getModulePath();
                break;
            case PathType::FILE:
                $path = $this->getPathObject()->getPathWithoutQuery();
                break;
            default:
                $path = $this->getPathObject()->getPathWithoutQuery();
                break;
        }

        $client = $this->getPathObject()->getClient();
        $timestamp = $timestamp ? $timestamp : $this->getPathObject()->getTimestamp();
        $ttl = $ttl ? $ttl : $this->getPathObject()->getTTL();

        return new ilWACToken($path, $client, $timestamp, $ttl);
    }


    /**
     * @param int $timestamp
     * @param int $ttl
     * @return void
     *
     * @throws \ilWACException
     */
    public function buildAndSetTokenInstance($timestamp = 0, $ttl = 0)
    {
        assert(is_int($timestamp));
        assert(is_int($ttl));

        $this->setTokenInstance($this->buildTokenInstance($timestamp, $ttl));
    }


    /**
     * @return int
     */
    public static function getTokenMaxLifetimeInSeconds()
    {
        return self::$token_max_lifetime_in_seconds;
    }


    /**
     * @param int $token_max_lifetime_in_seconds
     * @return void
     *
     * @throws \ilWACException
     */
    public static function setTokenMaxLifetimeInSeconds($token_max_lifetime_in_seconds)
    {
        assert(is_int($token_max_lifetime_in_seconds));
        if ($token_max_lifetime_in_seconds > self::MAX_LIFETIME) {
            throw new ilWACException(ilWACException::MAX_LIFETIME);
        }
        self::$token_max_lifetime_in_seconds = $token_max_lifetime_in_seconds;
    }


    /**
     * @return int
     */
    public static function getCookieMaxLifetimeInSeconds()
    {
        return self::$cookie_max_lifetime_in_seconds;
    }


    /**
     * @param int $cookie_max_lifetime_in_seconds
     *
     * @return void
     *
     * @throws \ilWACException
     */
    public static function setCookieMaxLifetimeInSeconds($cookie_max_lifetime_in_seconds)
    {
        assert(is_int($cookie_max_lifetime_in_seconds));
        if ($cookie_max_lifetime_in_seconds > self::MAX_LIFETIME) {
            throw new ilWACException(ilWACException::MAX_LIFETIME);
        }
        self::$cookie_max_lifetime_in_seconds = $cookie_max_lifetime_in_seconds;
    }


    /**
     * @return int
     */
    protected function getRelevantLifeTime()
    {
        $request_ttl = $this->getPathObject()->getTTL();
        if ($request_ttl > 0) {
            return $request_ttl;
        }
        switch ($this->getType()) {
            case PathType::FOLDER:
                $life_time = self::getCookieMaxLifetimeInSeconds();
                break;
            case PathType::FILE:
                $life_time = self::getTokenMaxLifetimeInSeconds();
                break;
            default:
                $life_time = 0;
                break;
        }

        return $life_time;
    }


    /**
     * @return bool
     */
    public function isChecked()
    {
        return (bool) $this->checked;
    }


    /**
     * @param bool $checked
     * @return void
     */
    public function setChecked($checked)
    {
        assert(is_bool($checked));
        $this->checked = $checked;
    }
}
