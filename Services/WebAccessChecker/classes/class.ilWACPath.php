<?php
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
// declare(strict_types=1);
/**
 * Class ilWACPath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACPath
{
    public const DIR_DATA = "data";
    public const DIR_SEC = "sec";
    /**
     * Copy this without to regex101.com and test with some URL of files
     */
    public const REGEX = "(?<prefix>.*?)(?<path>(?<path_without_query>(?<secure_path_id>(?<module_path>\/data\/(?<client>[\w\-\.]*)\/(?<sec>sec\/|)(?<module_type>.*?)\/(?<module_identifier>.*\/|)))(?<appendix>[^\?\n]*)).*)";
    /**
     * @var string[]
     */
    protected static array $image_suffixes = [
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
    ];
    /**
     * @var string[]
     */
    protected static array $video_suffixes = [
        'mp4',
        'm4v',
        'mov',
        'wmv',
        'webm',
    ];
    /**
     * @var string[]
     */
    protected static array $audio_suffixes = [
        'mp3',
        'aiff',
        'aif',
        'm4a',
        'wav',
    ];

    protected string $client = '';
    /**
     * @var string[]
     */
    protected array $parameters = [];
    protected bool $in_sec_folder = false;
    protected string $token = '';
    protected int $timestamp = 0;
    protected int $ttl = 0;
    protected string $secure_path = '';
    protected string $secure_path_id = '';
    protected string $original_request = '';
    protected string $file_name = '';
    protected string $query = '';
    protected string $suffix = '';
    protected string $prefix = '';
    protected string $appendix = '';
    protected string $module_path = '';
    protected string $path = '';
    protected string $module_type = '';
    protected string $module_identifier = '';
    protected string $path_without_query = '';

    public function __construct(string $path)
    {
        $this->setOriginalRequest($path);
        $re = '/' . self::REGEX . '/';
        preg_match($re, $path, $result);

        foreach ($result as $k => $v) {
            if (is_numeric($k)) {
                unset($result[$k]);
            }
        }

        $moduleId = strstr(
            !isset($result['module_identifier']) || is_null($result['module_identifier']) ? '' : $result['module_identifier'],
            '/',
            true
        );
        $moduleId = $moduleId === false ? '' : $moduleId;

        $this->setPrefix(!isset($result['prefix']) || is_null($result['prefix']) ? '' : $result['prefix']);
        $this->setClient(!isset($result['client']) || is_null($result['client']) ? '' : $result['client']);
        $this->setAppendix(!isset($result['appendix']) || is_null($result['appendix']) ? '' : $result['appendix']);
        $this->setModuleIdentifier($moduleId);
        $this->setModuleType(!isset($result['module_type']) || is_null($result['module_type']) ? '' : $result['module_type']);

        if ($this->getModuleIdentifier() !== '' && $this->getModuleIdentifier() !== '0') {
            $module_path = strstr(
                !isset($result['module_path']) || is_null($result['module_path']) ? '' : $result['module_path'],
                $this->getModuleIdentifier(),
                true
            );
            $module_path = '.' . ($module_path === false ? '' : $module_path);
        } else {
            $module_path = ('.' . (!isset($result['module_path']) || is_null($result['module_path']) ? '' : $result['module_path']));
        }

        $this->setModulePath($module_path);
        $this->setInSecFolder(isset($result['sec']) && $result['sec'] === 'sec/');
        $this->setPathWithoutQuery(
            '.' . (!isset($result['path_without_query']) || is_null($result['path_without_query']) ? '' : $result['path_without_query'])
        );
        $this->setPath('.' . (!isset($result['path']) || is_null($result['path']) ? '' : $result['path']));
        $this->setSecurePath(
            '.' . (!isset($result['secure_path_id']) || is_null($result['secure_path_id']) ? '' : $result['secure_path_id'])
        );
        $this->setSecurePathId(!isset($result['module_type']) || is_null($result['module_type']) ? '' : $result['module_type']);
        // Pathinfo
        $parts = parse_url($path);
        $this->setFileName(basename($parts['path']));
        if (isset($parts['query'])) {
            $parts_query = $parts['query'];
            $this->setQuery($parts_query);
            parse_str($parts_query, $query);
            $this->setParameters($query);
        }
        $this->setSuffix(pathinfo($parts['path'], PATHINFO_EXTENSION));
        $this->handleParameters();
    }

    protected function handleParameters() : void
    {
        $param = $this->getParameters();
        if (isset($param[ilWACSignedPath::WAC_TOKEN_ID])) {
            $this->setToken($param[ilWACSignedPath::WAC_TOKEN_ID]);
        }
        if (isset($param[ilWACSignedPath::WAC_TIMESTAMP_ID])) {
            $this->setTimestamp((int) $param[ilWACSignedPath::WAC_TIMESTAMP_ID]);
        }
        if (isset($param[ilWACSignedPath::WAC_TTL_ID])) {
            $this->setTTL((int) $param[ilWACSignedPath::WAC_TTL_ID]);
        }
    }

    /**
     * @return string[]
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * @param string[] $parameters
     */
    public function setParameters(array $parameters) : void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string[]
     */
    public static function getAudioSuffixes() : array
    {
        return self::$audio_suffixes;
    }

    /**
     * @param string[] $audio_suffixes
     */
    public static function setAudioSuffixes(array $audio_suffixes) : void
    {
        self::$audio_suffixes = $audio_suffixes;
    }

    /**
     * @return string[]
     */
    public static function getImageSuffixes() : array
    {
        return self::$image_suffixes;
    }

    /**
     * @param string[] $image_suffixes
     */
    public static function setImageSuffixes(array $image_suffixes) : void
    {
        self::$image_suffixes = $image_suffixes;
    }

    /**
     * @return string[]
     */
    public static function getVideoSuffixes() : array
    {
        return self::$video_suffixes;
    }

    /**
     * @param string[] $video_suffixes
     */
    public static function setVideoSuffixes(array $video_suffixes) : void
    {
        self::$video_suffixes = $video_suffixes;
    }

    public function getPrefix() : string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix) : void
    {
        $this->prefix = $prefix;
    }

    public function getAppendix() : string
    {
        return $this->appendix;
    }

    public function setAppendix(string $appendix) : void
    {
        $this->appendix = $appendix;
    }

    public function getModulePath() : string
    {
        return $this->module_path;
    }

    public function setModulePath(string $module_path) : void
    {
        $this->module_path = $module_path;
    }

    public function getDirName() : string
    {
        return dirname($this->getPathWithoutQuery());
    }

    public function getPathWithoutQuery() : string
    {
        return $this->path_without_query;
    }

    public function setPathWithoutQuery(string $path_without_query) : void
    {
        $this->path_without_query = $path_without_query;
    }

    public function isImage() : bool
    {
        return in_array(strtolower($this->getSuffix()), self::$image_suffixes);
    }

    public function getSuffix() : string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix) : void
    {
        $this->suffix = $suffix;
    }

    public function isStreamable() : bool
    {
        return ($this->isAudio() || $this->isVideo());
    }

    public function isAudio() : bool
    {
        return in_array(strtolower($this->getSuffix()), self::$audio_suffixes);
    }

    public function isVideo() : bool
    {
        return in_array(strtolower($this->getSuffix()), self::$video_suffixes);
    }

    public function fileExists() : bool
    {
        return is_file($this->getPathWithoutQuery());
    }

    public function hasToken() : bool
    {
        return ($this->token !== '');
    }

    public function hasTimestamp() : bool
    {
        return ($this->timestamp !== 0);
    }

    public function hasTTL() : bool
    {
        return ($this->ttl !== 0);
    }

    public function getToken() : string
    {
        return $this->token;
    }

    public function setToken(string $token) : void
    {
        $this->parameters[ilWACSignedPath::WAC_TOKEN_ID] = $token;
        $this->token = $token;
    }

    public function getTimestamp() : int
    {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp) : void
    {
        $this->parameters[ilWACSignedPath::WAC_TIMESTAMP_ID] = $timestamp;
        $this->timestamp = $timestamp;
    }

    public function getTTL() : int
    {
        return $this->ttl;
    }

    public function setTTL(int $ttl) : void
    {
        $this->parameters[ilWACSignedPath::WAC_TTL_ID] = $ttl;
        $this->ttl = $ttl;
    }

    public function getClient() : string
    {
        return $this->client;
    }

    public function setClient(string $client) : void
    {
        $this->client = $client;
    }

    public function getSecurePathId() : string
    {
        return $this->secure_path_id;
    }

    public function setSecurePathId(string $secure_path_id) : void
    {
        $this->secure_path_id = $secure_path_id;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Returns a clean (everything behind ? is removed and rawurldecoded path
     */
    public function getCleanURLdecodedPath() : string
    {
        $path = explode("?", $this->path); // removing everything behind ?
        return rawurldecode($path[0]);
    }

    public function setPath(string $path) : void
    {
        $this->path = $path;
    }

    public function getQuery() : string
    {
        return $this->query;
    }

    public function setQuery(string $query) : void
    {
        $this->query = $query;
    }

    public function getFileName() : string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name) : void
    {
        $this->file_name = $file_name;
    }

    public function getOriginalRequest() : string
    {
        return $this->original_request;
    }

    public function setOriginalRequest(string $original_request) : void
    {
        $this->original_request = $original_request;
    }

    public function getSecurePath() : string
    {
        return $this->secure_path;
    }

    public function setSecurePath(string $secure_path) : void
    {
        $this->secure_path = $secure_path;
    }

    public function isInSecFolder() : bool
    {
        return $this->in_sec_folder;
    }

    public function setInSecFolder(bool $in_sec_folder) : void
    {
        $this->in_sec_folder = $in_sec_folder;
    }

    public function getModuleType() : string
    {
        return $this->module_type;
    }

    public function setModuleType(string $module_type) : void
    {
        $this->module_type = $module_type;
    }

    public function getModuleIdentifier() : string
    {
        return $this->module_identifier;
    }

    public function setModuleIdentifier(string $module_identifier) : void
    {
        $this->module_identifier = $module_identifier;
    }
}
