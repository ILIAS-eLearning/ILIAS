<?php

// declare(strict_types=1);

use ILIAS\HTTP\Cookies\CookieFactory;
use ILIAS\HTTP\Cookies\CookieFactoryImpl;
use ILIAS\HTTP\Services;
use ILIAS\WebAccessChecker\PathType;
use ILIAS\HTTP\GlobalHttpState;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class ilWACSignedPath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACSignedPath
{
    public const WAC_TOKEN_ID = 'il_wac_token';
    public const WAC_TIMESTAMP_ID = 'il_wac_ts';
    public const WAC_TTL_ID = 'il_wac_ttl';
    public const TS_SUFFIX = 'ts';
    public const TTL_SUFFIX = 'ttl';
    public const MAX_LIFETIME = 600;

    protected ?ilWACPath $path_object = null;
    protected ?ilWACToken $token_instance = null;
    protected int $type = PathType::FILE;
    protected static int $token_max_lifetime_in_seconds = 3;
    protected static int $cookie_max_lifetime_in_seconds = 300;
    protected bool $checked = false;
    private GlobalHttpState $httpService;
    private CookieFactory $cookieFactory;

    /**
     * ilWACSignedPath constructor.
     */
    public function __construct(ilWACPath $ilWACPath, GlobalHttpState $httpState, CookieFactory $cookieFactory)
    {
        $this->setPathObject($ilWACPath);
        $this->httpService = $httpState;
        $this->cookieFactory = $cookieFactory;
    }

    /**
     * @throws ilWACException
     */
    public function getSignedPath(): string
    {
        if ($this->getType() !== PathType::FILE) {
            throw new ilWACException(ilWACException::WRONG_PATH_TYPE);
        }
        if ($this->getPathObject()->getOriginalRequest() === '' || $this->getPathObject()->getOriginalRequest() === '0') {
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

        $path .= '&' . self::WAC_TTL_ID . '=' . $this->getTokenInstance()->getTTL();

        return $path . '&' . self::WAC_TIMESTAMP_ID . '='
            . $this->getTokenInstance()->getTimestamp();
    }

    public function isFolderSigned(): bool
    {
        $this->httpService->cookieJar();

        $this->setType(PathType::FOLDER);
        $plain_token = $this->buildTokenInstance();
        $name = $plain_token->getHashedId();

        // Token
        $default_token = '';
        $token_cookie_value = $this->httpService->request()->getCookieParams()[$name] ?? $default_token;
        // Timestamp
        $default_timestamp = 0;
        $timestamp_cookie_value = $this->httpService->request()->getCookieParams()[$name . self::TS_SUFFIX] ?? $default_timestamp;
        $timestamp_cookie_value = (int) $timestamp_cookie_value;
        // TTL
        $default_ttl = 0;
        $ttl_cookie_value = $this->httpService->request()->getCookieParams()[$name . self::TTL_SUFFIX] ?? $default_ttl;
        $ttl_cookie_value = (int) $ttl_cookie_value;

        $this->getPathObject()->setToken($token_cookie_value);
        $this->getPathObject()->setTimestamp($timestamp_cookie_value);
        $this->getPathObject()->setTTL($ttl_cookie_value);
        $this->buildAndSetTokenInstance();

        return $this->getPathObject()->hasToken();
    }

    /**
     * @throws ilWACException
     */
    public function isFolderTokenValid(): bool
    {
        if (!$this->isFolderSigned()) {
            return false;
        }

        return $this->checkToken();
    }

    protected function saveFolderToken(): void
    {
        $ttl = $this->getPathObject()->getTTL();
        $cookie_lifetime = $ttl !== 0 ? $ttl : self::getCookieMaxLifetimeInSeconds();
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

        $jar = $this->httpService->cookieJar()->with($tokenCookie)
                                 ->with($timestampCookie)
                                 ->with($ttlCookie);

        // FIX: currently the cookies are never stored, we must use setcookie
        foreach ($jar->getAll() as $cookie) {
            setcookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpires(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->getSecure(),
                $cookie->getHttpOnly()
            );
        }
    }

    public function revalidatingFolderToken(): bool
    {
        if ($this->getType() !== PathType::FOLDER) {
            return false;
        }
        $this->buildAndSetTokenInstance(time(), $this->getPathObject()->getTTL());
        $this->getPathObject()->setTTL($this->getTokenInstance()->getTTL());
        $this->getPathObject()->setTimestamp($this->getTokenInstance()->getTimestamp());
        $this->getPathObject()->setToken($this->getTokenInstance()->getToken());

        $this->saveFolderToken();

        return true;
    }

    public function isSignedPath(): bool
    {
        return ($this->getPathObject()->hasToken() && $this->getPathObject()->hasTimestamp()
            && $this->getPathObject()->hasTTL());
    }

    /**
     * @throws ilWACException
     */
    public function isSignedPathValid(): bool
    {
        $this->buildAndSetTokenInstance($this->getPathObject()->getTimestamp(), $this->getPathObject()->getTTL());

        return $this->checkToken();
    }

    /**
     *
     *
     * @throws ilWACException
     */
    public static function signFile(string $path_to_file): string
    {
        global $DIC;
        if ($path_to_file === '' || $path_to_file === '0') {
            return '';
        }
        $ilWACPath = new ilWACPath($path_to_file);
        if ($ilWACPath->getClient() === '' || $ilWACPath->getClient() === '0') {
            return $path_to_file;
        }
        $obj = new self($ilWACPath, $DIC->http(), new CookieFactoryImpl());
        $obj->setType(PathType::FILE);
        $obj->buildAndSetTokenInstance(time(), self::getTokenMaxLifetimeInSeconds());

        return $obj->getSignedPath();
    }

    public static function signFolderOfStartFile(string $start_file_path): void
    {
        global $DIC;
        $obj = new self(new ilWACPath($start_file_path), $DIC->http(), new CookieFactoryImpl());
        $obj->setType(PathType::FOLDER);
        $obj->buildAndSetTokenInstance(time(), self::getCookieMaxLifetimeInSeconds());
        $obj->saveFolderToken();
    }

    public function getTokenInstance(): ?\ilWACToken
    {
        return $this->token_instance;
    }

    public function setTokenInstance(ilWACToken $token_instance): void
    {
        $this->token_instance = $token_instance;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getPathObject(): ?\ilWACPath
    {
        return $this->path_object;
    }

    public function setPathObject(ilWACPath $path_object): void
    {
        $this->path_object = $path_object;
    }

    /**
     * @throws \ilWACException
     */
    protected function checkToken(): bool
    {
        $request_token_string = $this->getPathObject()->getToken();
        $request_ttl = $this->getPathObject()->getTTL();
        $request_timestamp = $this->getPathObject()->getTimestamp();
        $current_timestamp = time();

        $timestamp_valid = ($current_timestamp < ($request_timestamp + $request_ttl));

        if (!$timestamp_valid) {
            $this->setChecked(true);

            return false;
        }

        $simulated_token = $this->buildTokenInstance($request_timestamp, $request_ttl);
        $simulated_token_string = $simulated_token->getToken();
        $token_valid = ($simulated_token_string === $request_token_string);

        if (!$token_valid) {
            $this->setChecked(true);

            return false;
        }

        return true;
    }

    /**
     *
     * @throws ilWACException
     */
    protected function buildTokenInstance(int $timestamp = 0, int $ttl = 0): \ilWACToken
    {
        if ($this->getType() === 0) {
            throw new ilWACException(ilWACException::CODE_NO_TYPE);
        }

        switch ($this->getType()) {
            case PathType::FOLDER:
                $path = $this->getPathObject()->getSecurePath();
                break;
            default:
                $path = $this->getPathObject()->getPathWithoutQuery();
                break;
        }

        $client = $this->getPathObject()->getClient();
        $timestamp = $timestamp !== 0 ? $timestamp : $this->getPathObject()->getTimestamp();
        $ttl = $ttl !== 0 ? $ttl : $this->getPathObject()->getTTL();

        return new ilWACToken($path, $client, $timestamp, $ttl);
    }

    /**
     *
     * @throws \ilWACException
     */
    public function buildAndSetTokenInstance(int $timestamp = 0, int $ttl = 0): void
    {
        $this->setTokenInstance($this->buildTokenInstance($timestamp, $ttl));
    }

    public static function getTokenMaxLifetimeInSeconds(): int
    {
        return self::$token_max_lifetime_in_seconds;
    }

    /**
     *
     * @throws \ilWACException
     */
    public static function setTokenMaxLifetimeInSeconds(int $token_max_lifetime_in_seconds): void
    {
        if ($token_max_lifetime_in_seconds > self::MAX_LIFETIME) {
            throw new ilWACException(ilWACException::MAX_LIFETIME);
        }
        self::$token_max_lifetime_in_seconds = $token_max_lifetime_in_seconds;
    }

    public static function getCookieMaxLifetimeInSeconds(): int
    {
        return self::$cookie_max_lifetime_in_seconds;
    }

    /**
     *
     *
     * @throws \ilWACException
     */
    public static function setCookieMaxLifetimeInSeconds(int $cookie_max_lifetime_in_seconds): void
    {
        if ($cookie_max_lifetime_in_seconds > self::MAX_LIFETIME) {
            throw new ilWACException(ilWACException::MAX_LIFETIME);
        }
        self::$cookie_max_lifetime_in_seconds = $cookie_max_lifetime_in_seconds;
    }

    protected function getRelevantLifeTime(): int
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

    public function isChecked(): bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
    }
}
