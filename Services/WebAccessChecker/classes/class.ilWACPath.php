<?php
// declare(strict_types=1);

/**
 * Class ilWACPath
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilWACPath
{
    const DIR_DATA = "data";
    const DIR_SEC = "sec";
    /**
     * Copy this without to regex101.com and test with some URL of files
     */
    const REGEX = "(?<prefix>.*?)(?<path>(?<path_without_query>(?<secure_path_id>(?<module_path>\/data\/(?<client>[\w\-\.]*)\/(?<sec>sec\/|)(?<module_type>.*?)\/(?<module_identifier>.*\/|)))(?<appendix>[^\?\n]*)).*)";
    /**
     * @var string[]
     */
    protected static $image_suffixes = array(
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
    );
    /**
     * @var string[]
     */
    protected static $video_suffixes = array(
        'mp4',
        'm4v',
        'mov',
        'wmv',
        'webm',
    );
    /**
     * @var string[]
     */
    protected static $audio_suffixes = array(
        'mp3',
        'aiff',
        'aif',
        'm4a',
        'wav',
    );
    /**
     * @var string
     */
    protected $client = '';
    /**
     * @var array
     */
    protected $parameters = array();
    /**
     * @var bool
     */
    protected $in_sec_folder = false;
    /**
     * @var string
     */
    protected $token = '';
    /**
     * @var int
     */
    protected $timestamp = 0;
    /**
     * @var int
     */
    protected $ttl = 0;
    /**
     * @var string
     */
    protected $secure_path = '';
    /**
     * @var string
     */
    protected $secure_path_id = '';
    /**
     * @var string
     */
    protected $original_request = '';
    /**
     * @var string
     */
    protected $file_name = '';
    /**
     * @var string
     */
    protected $query = '';
    /**
     * @var string
     */
    protected $suffix = '';
    /**
     * @var string
     */
    protected $prefix = '';
    /**
     * @var string
     */
    protected $appendix = '';
    /**
     * @var string
     */
    protected $module_path = '';
    /**
     * @var string
     */
    protected $path = '';
    /**
     * @var string
     */
    protected $module_type = '';
    /**
     * @var string
     */
    protected $module_identifier = '';
    /**
     * @var string
     */
    protected $path_without_query = '';


    /**
     * ilWACPath constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        assert(is_string($path));
        $this->setOriginalRequest($path);
        $re = '/' . self::REGEX . '/';
        preg_match($re, $path, $result);

        foreach ($result as $k => $v) {
            if (is_numeric($k)) {
                unset($result[$k]);
            }
        }

        $moduleId = strstr(is_null($result['module_identifier']) ? '' : $result['module_identifier'], "/", true);
        $moduleId = $moduleId === false ? '' : $moduleId;

        $this->setPrefix(is_null($result['prefix']) ? '' : $result['prefix']);
        $this->setClient(is_null($result['client']) ? '' : $result['client']);
        $this->setAppendix(is_null($result['appendix']) ? '' : $result['appendix']);
        $this->setModuleIdentifier($moduleId);
        $this->setModuleType(is_null($result['module_type']) ? '' : $result['module_type']);

        $modulePath = null;

        if ($this->getModuleIdentifier()) {
            $modulePath = strstr(is_null($result['module_path']) ? '' : $result['module_path'], $this->getModuleIdentifier(), true);
            $modulePath = '.' . ($modulePath === false ? '' : $modulePath);
        } else {
            $modulePath = ('.' . (is_null($result['module_path']) ? '' : $result['module_path']));
        }

        $this->setModulePath("$modulePath");
        $this->setInSecFolder($result['sec'] === 'sec/');
        $this->setPathWithoutQuery('.'
                                   . (is_null($result['path_without_query']) ? '' : $result['path_without_query']));
        $this->setPath('.' . (is_null($result['path']) ? '' : $result['path']));
        $this->setSecurePath('.'
                             . (is_null($result['secure_path_id']) ? '' : $result['secure_path_id']));
        $this->setSecurePathId(is_null($result['module_type']) ? '' : $result['module_type']);
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


    protected function handleParameters()
    {
        $param = $this->getParameters();
        if (isset($param[ilWACSignedPath::WAC_TOKEN_ID])) {
            $this->setToken($param[ilWACSignedPath::WAC_TOKEN_ID]);
        }
        if (isset($param[ilWACSignedPath::WAC_TIMESTAMP_ID])) {
            $this->setTimestamp(intval($param[ilWACSignedPath::WAC_TIMESTAMP_ID]));
        }
        if (isset($param[ilWACSignedPath::WAC_TTL_ID])) {
            $this->setTTL(intval($param[ilWACSignedPath::WAC_TTL_ID]));
        }
    }


    /**
     * @return array
     */
    public function getParameters()
    {
        return (array) $this->parameters;
    }


    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }


    /**
     * @return array
     */
    public static function getAudioSuffixes()
    {
        return (array) self::$audio_suffixes;
    }


    /**
     * @param array $audio_suffixes
     */
    public static function setAudioSuffixes(array $audio_suffixes)
    {
        self::$audio_suffixes = $audio_suffixes;
    }


    /**
     * @return array
     */
    public static function getImageSuffixes()
    {
        return (array) self::$image_suffixes;
    }


    /**
     * @param array $image_suffixes
     */
    public static function setImageSuffixes(array $image_suffixes)
    {
        self::$image_suffixes = $image_suffixes;
    }


    /**
     * @return array
     */
    public static function getVideoSuffixes()
    {
        return (array) self::$video_suffixes;
    }


    /**
     * @param array $video_suffixes
     */
    public static function setVideoSuffixes(array $video_suffixes)
    {
        self::$video_suffixes = $video_suffixes;
    }


    /**
     * @return string
     */
    public function getPrefix()
    {
        return (string) $this->prefix;
    }


    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        assert(is_string($prefix));
        $this->prefix = $prefix;
    }


    /**
     * @return string
     */
    public function getAppendix()
    {
        return (string) $this->appendix;
    }


    /**
     * @param string $appendix
     */
    public function setAppendix($appendix)
    {
        assert(is_string($appendix));
        $this->appendix = $appendix;
    }


    /**
     * @return string
     */
    public function getModulePath()
    {
        return (string) $this->module_path;
    }


    /**
     * @param string $module_path
     */
    public function setModulePath($module_path)
    {
        assert(is_string($module_path));
        $this->module_path = $module_path;
    }


    /**
     * @return string
     */
    public function getDirName()
    {
        return (string) dirname($this->getPathWithoutQuery());
    }


    /**
     * @return string
     */
    public function getPathWithoutQuery()
    {
        return (string) $this->path_without_query;
    }


    /**
     * @param string $path_without_query
     */
    public function setPathWithoutQuery($path_without_query)
    {
        assert(is_string($path_without_query));
        $this->path_without_query = $path_without_query;
    }


    /**
     * @return bool
     */
    public function isImage()
    {
        return (bool) in_array(strtolower($this->getSuffix()), self::$image_suffixes);
    }


    /**
     * @return string
     */
    public function getSuffix()
    {
        return (string) $this->suffix;
    }


    /**
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        assert(is_string($suffix));
        $this->suffix = $suffix;
    }


    /**
     * @return bool
     */
    public function isStreamable()
    {
        return (bool) ($this->isAudio() || $this->isVideo());
    }


    /**
     * @return bool
     */
    public function isAudio()
    {
        return (bool) in_array(strtolower($this->getSuffix()), self::$audio_suffixes);
    }


    /**
     * @return bool
     */
    public function isVideo()
    {
        return (bool) in_array(strtolower($this->getSuffix()), self::$video_suffixes);
    }


    /**
     * @return bool
     */
    public function fileExists()
    {
        return (bool) is_file($this->getPathWithoutQuery());
    }


    /**
     * @return bool
     */
    public function hasToken()
    {
        return (bool) ($this->token !== '');
    }


    /**
     * @return bool
     */
    public function hasTimestamp()
    {
        return (bool) ($this->timestamp !== 0);
    }


    /**
     * @return bool
     */
    public function hasTTL()
    {
        return (bool) ($this->ttl !== 0);
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
     */
    public function setToken($token)
    {
        assert(is_string($token));
        $this->parameters[ilWACSignedPath::WAC_TOKEN_ID] = $token;
        $this->token = $token;
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
     */
    public function setTimestamp($timestamp)
    {
        assert(is_int($timestamp));
        $this->parameters[ilWACSignedPath::WAC_TIMESTAMP_ID] = $timestamp;
        $this->timestamp = $timestamp;
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
     */
    public function setTTL($ttl)
    {
        $this->parameters[ilWACSignedPath::WAC_TTL_ID] = $ttl;
        $this->ttl = $ttl;
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
     */
    public function setClient($client)
    {
        assert(is_string($client));
        $this->client = $client;
    }


    /**
     * @return string
     */
    public function getSecurePathId()
    {
        return (string) $this->secure_path_id;
    }


    /**
     * @param string $secure_path_id
     */
    public function setSecurePathId($secure_path_id)
    {
        assert(is_string($secure_path_id));
        $this->secure_path_id = $secure_path_id;
    }


    /**
     * @return string
     */
    public function getPath()
    {
        return (string) $this->path;
    }


    /**
     * Returns a clean (everything behind ? is removed and rawurldecoded path
     *
     * @return string
     */
    public function getCleanURLdecodedPath()
    {
        $path = explode("?", (string) $this->path); // removing everything behind ?
        $path_to_file = rawurldecode($path[0]);

        return $path_to_file;
    }


    /**
     * @param string $path
     */
    public function setPath($path)
    {
        assert(is_string($path));
        $this->path = $path;
    }


    /**
     * @return string
     */
    public function getQuery()
    {
        return (string) $this->query;
    }


    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        assert(is_string($query));
        $this->query = $query;
    }


    /**
     * @return string
     */
    public function getFileName()
    {
        return (string) $this->file_name;
    }


    /**
     * @param string $file_name
     */
    public function setFileName($file_name)
    {
        assert(is_string($file_name));
        $this->file_name = $file_name;
    }


    /**
     * @return string
     */
    public function getOriginalRequest()
    {
        return (string) $this->original_request;
    }


    /**
     * @param string $original_request
     */
    public function setOriginalRequest($original_request)
    {
        assert(is_string($original_request));
        $this->original_request = $original_request;
    }


    /**
     * @return string
     */
    public function getSecurePath()
    {
        return (string) $this->secure_path;
    }


    /**
     * @param string $secure_path
     */
    public function setSecurePath($secure_path)
    {
        assert(is_string($secure_path));
        $this->secure_path = $secure_path;
    }


    /**
     * @return bool
     */
    public function isInSecFolder()
    {
        return (bool) $this->in_sec_folder;
    }


    /**
     * @param bool $in_sec_folder
     */
    public function setInSecFolder($in_sec_folder)
    {
        assert(is_bool($in_sec_folder));
        $this->in_sec_folder = $in_sec_folder;
    }


    /**
     * @return string
     */
    public function getModuleType()
    {
        return (string) $this->module_type;
    }


    /**
     * @param string $module_type
     */
    public function setModuleType($module_type)
    {
        assert(is_string($module_type));
        $this->module_type = $module_type;
    }


    /**
     * @return string
     */
    public function getModuleIdentifier()
    {
        return (string) $this->module_identifier;
    }


    /**
     * @param string $module_identifier
     */
    public function setModuleIdentifier($module_identifier)
    {
        assert(is_string($module_identifier));
        $this->module_identifier = $module_identifier;
    }
}
