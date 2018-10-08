<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PEAR_test_mock_pearweb.php.inc';
$GLOBALS['pearweb'] = new PEAR_test_mock_pearweb;

require_once 'PEAR/REST.php';
class test_PEAR_REST extends PEAR_REST {
    function downloadHttp($url, $lastmodified = null, $accept = false, $channel = false)
    {
        $info = parse_url($url);
        if (!isset($info['scheme']) || !in_array($info['scheme'], array('http', 'https'))) {
            return PEAR::raiseError('Cannot download non-http URL "' . $url . '"');
        }

        if (!isset($info['host'])) {
            return PEAR::raiseError('Cannot download from non-URL "' . $url . '"');
        } else {
            $host = @$info['host'];
            $port = @$info['port'];
            $path = @$info['path'];
        }
        $proxy_host = $proxy_port = $proxy_user = $proxy_pass = '';
        if ($this->config->get('http_proxy')&&
              $proxy = parse_url($this->config->get('http_proxy'))) {
            $proxy_host = @$proxy['host'];
            if (isset($proxy['scheme']) && $proxy['scheme'] == 'https') {
                $proxy_host = 'ssl://' . $proxy_host;
            }
            $proxy_port = @$proxy['port'];
            $proxy_user = @$proxy['user'];
            $proxy_pass = @$proxy['pass'];

            if ($proxy_port == '') {
                $proxy_port = 8080;
            }
        }
        if (empty($port)) {
            if (isset($info['scheme']) && $info['scheme'] == 'https') {
                $port = 443;
            } else {
                $port = 80;
            }
        }
        $request = "GET $path HTTP/1.1\r\n";
        $ifmodifiedsince = '';
        if (is_array($lastmodified)) {
            if (isset($lastmodified['Last-Modified'])) {
                $ifmodifiedsince = 'If-Modified-Since: ' . $lastmodified['Last-Modified'] . "\r\n";
            }
            if (isset($lastmodified['ETag'])) {
                $ifmodifiedsince .= "If-None-Match: $lastmodified[ETag]\r\n";
            }
        } else {
            $ifmodifiedsince = ($lastmodified ? "If-Modified-Since: $lastmodified\r\n" : '');
        }
        $request .= "Host: $host:$port\r\n" . $ifmodifiedsince .
            "User-Agent: PHP/" . PHP_VERSION . "\r\n";
        $username = $this->config->get('username');
        $password = $this->config->get('password');
        if ($username && $password) {
            $tmp = base64_encode("$username:$password");
            $request .= "Authorization: Basic $tmp\r\n";
        }
        if ($proxy_host != '' && $proxy_user != '') {
            $request .= 'Proxy-Authorization: Basic ' .
                base64_encode($proxy_user . ':' . $proxy_pass) . "\r\n";
        }
        if ($accept) {
            $request .= 'Accept: ' . implode(', ', $accept) . "\r\n";
        }
        $request .= "Connection: close\r\n";
        $request .= "\r\n";
        $headers = array();
        $retrieved = explode("\n", $GLOBALS['pearweb']->receiveREST($url));
        $headers = array();
        $line = array_shift($retrieved);
        while (strlen(trim($line))) {
            if (preg_match('/^([^:]+):\s+(.*)\s*$/', $line, $matches)) {
                $headers[strtolower($matches[1])] = trim($matches[2]);
            } elseif (preg_match('|^HTTP/1.[01] ([0-9]{3}) |', $line, $matches)) {
                if ($matches[1] == 304 && ($lastmodified || ($lastmodified === false))) {
                    return false;
                }
                if ($matches[1] != 200) {
                    return PEAR::raiseError("File http://$host:$port$path not valid (received: $line)");
                }
            }
            $line = array_shift($retrieved);
        }
        $data = join("\n", $retrieved);
        if (isset($headers['content-length'])) {
            $length = $headers['content-length'];
        } else {
            $length = -1;
        }
        if ($lastmodified === false || $lastmodified) {
            if (isset($headers['etag'])) {
                $lastmodified = array('ETag' => $headers['etag']);
            }
            if (isset($headers['last-modified'])) {
                if (is_array($lastmodified)) {
                    $lastmodified['Last-Modified'] = $headers['last-modified'];
                } else {
                    $lastmodified = $headers['last-modified'];
                }
            }
            return array($data, $lastmodified, $headers);
        }
        return $data;
    }
}

require_once 'PEAR/REST/10.php';
class test_PEAR_REST_10 extends PEAR_REST_10 {
    function __construct($config, $options = array())
    {
        $this->_rest = new test_PEAR_REST($config, $options);
    }
}

require_once 'PEAR/REST/13.php';
class test_PEAR_REST_13 extends PEAR_REST_13 {
    function __construct($config, $options = array())
    {
        $this->_rest = new test_PEAR_REST($config, $options);
    }
}

require_once 'PEAR/Installer.php';
class test_PEAR_Installer extends PEAR_Installer {
    function &download($packages)
    {
        // trickiness: initialize here
        $this->PEAR_Downloader($this->ui, $options, $config);
        $ret = PEAR_Downloader::download($packages);
        $errors = $this->getErrorMsgs();
        $installpackages = $this->getDownloadedPackages();
        trigger_error("PEAR Warning: PEAR_Installer::download() is deprecated " .
                      "in favor of PEAR_Downloader class", E_USER_WARNING);
        return $ret;
    }

    /**
     * For simpler unit-testing
     * @param PEAR_Config
     * @param int
     * @param string
     */
    function &getPackagefileObject(&$c, $d, $dir = null)
    {
        $a = new test_PEAR_PackageFile($c, $d, $dir);
        return $a;
    }

    function downloadHttp(
        $url, &$ui, $save_dir = '.', $callback = null, $lastmodified = null,
        $accept = false, $channel = false
    ) {
//        return parent::downloadHttp($url, $ui, $save_dir, $callback);
        if ($callback) {
            call_user_func($callback, 'setup', array(&$ui));
        }
        $info = parse_url($url);
        if (!isset($info['scheme']) || $info['scheme'] != 'http') {
            return PEAR::raiseError('Cannot download non-http URL "' . $url . '"');
        }
        if (!isset($info['host'])) {
            return PEAR::raiseError('Cannot download from non-URL "' . $url . '"');
        } else {
            $host = @$info['host'];
            $port = @$info['port'];
            $path = @$info['path'];
        }
        if (isset($this)) {
            $config = &$this->config;
        } else {
            $config = &PEAR_Config::singleton();
        }
        $proxy_host = $proxy_port = $proxy_user = $proxy_pass = '';
        if ($proxy = parse_url($config->get('http_proxy'))) {
            $proxy_host = @$proxy['host'];
            $proxy_port = @$proxy['port'];
            $proxy_user = @$proxy['user'];
            $proxy_pass = @$proxy['pass'];

            if ($proxy_port == '') {
                $proxy_port = 8080;
            }
            if ($callback) {
                call_user_func($callback, 'message', "Using HTTP proxy $host:$port");
            }
        }
        if (empty($port)) {
            $port = 80;
        }
        // use _pearweb to get file
        $retrieved = explode("\n", $GLOBALS['pearweb']->receiveHttp($url));
        $headers = array();
        $line = array_shift($retrieved);
        while (strlen(trim($line))) {
            if (preg_match('/^([^:]+):\s+(.*)\s*$/', $line, $matches)) {
                $headers[strtolower($matches[1])] = trim($matches[2]);
            } elseif (preg_match('|^HTTP/1.[01] ([0-9]{3}) |', $line, $matches)) {
                if ($matches[1] != 200) {
                    return PEAR::raiseError("File http://$host:$port$path not valid (received: $line)");
                }
            }
            $line = array_shift($retrieved);
        }
        $retrieved = join("\n", $retrieved);
        if (isset($headers['content-disposition']) &&
            preg_match('/\sfilename=\"([^;]*\S)\"\s*(;|$)/', $headers['content-disposition'], $matches)) {
            $save_as = basename($matches[1]);
        } else {
            $save_as = basename($url);
        }
        if ($callback) {
            $tmp = call_user_func($callback, 'saveas', $save_as);
            if ($tmp) {
                $save_as = $tmp;
            }
        }
        $dest_file = $save_dir . DIRECTORY_SEPARATOR . $save_as;
        if (!$wp = @fopen($dest_file, 'wb')) {
            fclose($fp);
            if ($callback) {
                call_user_func($callback, 'writefailed', array($dest_file, $php_errormsg));
            }
            return PEAR::raiseError("could not open $dest_file for writing");
        }
        if (isset($headers['content-length'])) {
            $length = $headers['content-length'];
        } else {
            $length = -1;
        }
        $bytes = 0;
        if ($callback) {
            call_user_func($callback, 'start', array(basename($dest_file), $length));
        }
        $start = 0;
        while ($start < strlen($retrieved) - 1) {
            $data = substr($retrieved, $start, 1024);
            $start += 1024;
            $bytes += strlen($data);
            if ($callback) {
                call_user_func($callback, 'bytesread', $bytes);
            }
            if (!@fwrite($wp, $data)) {
                if ($callback) {
                    call_user_func($callback, 'writefailed', array($dest_file, $php_errormsg));
                }
                return PEAR::raiseError("$dest_file: write failed ($php_errormsg)");
            }
        }
        fclose($wp);
        if ($callback) {
            call_user_func($callback, 'done', $bytes);
        }
        return $dest_file;
    }

    function log($level, $msg, $append_crlf = true)
    {
        global $fakelog;
        if (isset($fakelog)) {
            $fakelog->log($level, $msg);
        } else {
            return parent::log($level, $msg);
        }
    }

    function &getDependency2($c, $i, $p, $s)
    {
        $a = &test_PEAR_Dependency2::singleton($c, $i, $p, $s);
        return $a;
    }

    /**
     * For simpler unit-testing
     * @param PEAR_Config
     * @param array
     * @param array
     * @param int
     */
    function &getDependency2Object(&$c, $i, $p, $s)
    {
        $z = &test_PEAR_Dependency2::singleton($c, $i, $p, $s);
        return $z;
    }
}

require_once 'PEAR/Downloader.php';
class test_PEAR_Downloader extends PEAR_Downloader {
    function __construct(&$ui, $options, &$config)
    {
        parent::__construct($ui, $options, $config);
    }

    /**
     * For simpler unit-testing
     * @param PEAR_Config
     * @param int
     * @param string
     */
    function &getPackagefileObject(&$c, $d, $dir = null)
    {
        $a = new test_PEAR_PackageFile($c, $d, $dir);
        return $a;
    }

    function downloadHttp(
        $url, &$ui, $save_dir = '.', $callback = null, $lastmodified = null,
        $accept = false, $channel = false
    ) {
        if (isset($GLOBALS['return304'])) {
            return false;
        }
//        return parent::downloadHttp($url, $ui, $save_dir, $callback);
        if ($callback) {
            call_user_func($callback, 'setup', array(&$ui));
        }

        $info = parse_url($url);
        $info = parse_url($url);
        if (!isset($info['scheme']) || !in_array($info['scheme'], array('http', 'https'))) {
            return PEAR::raiseError('Cannot download non-http URL "' . $url . '"');
        }

        if (!isset($info['host'])) {
            return PEAR::raiseError('Cannot download from non-URL "' . $url . '"');
        }

        $host = isset($info['host']) ? $info['host'] : null;
        $port = isset($info['port']) ? $info['port'] : null;
        $path = isset($info['path']) ? $info['path'] : null;

        if (isset($this)) {
            $config = &$this->config;
        } else {
            $config = &PEAR_Config::singleton();
        }

        $proxy_host = $proxy_port = $proxy_user = $proxy_pass = '';
        if ($config->get('http_proxy') && $proxy = parse_url($config->get('http_proxy'))) {
            $proxy_host = isset($proxy['host']) ? $proxy['host'] : null;
            if (isset($proxy['scheme']) && $proxy['scheme'] == 'https') {
                $proxy_host = 'ssl://' . $proxy_host;
            }
            $proxy_port = isset($proxy['port']) ? $proxy['port'] : 8080;
            $proxy_user = isset($proxy['user']) ? urldecode($proxy['user']) : null;
            $proxy_pass = isset($proxy['pass']) ? urldecode($proxy['pass']) : null;

            if ($callback) {
                call_user_func($callback, 'message', "Using HTTP proxy $host:$port");
            }
        }

        if (empty($port)) {
            $port = (isset($info['scheme']) && $info['scheme'] == 'https') ? 443 : 80;
        }

        $scheme = (isset($info['scheme']) && $info['scheme'] == 'https') ? 'https' : 'http';

        // use _pearweb to get file
        $retrieved = explode("\n", $GLOBALS['pearweb']->receiveHttp($url));

        $headers = array();
        $line = array_shift($retrieved);
        while (strlen(trim($line))) {
            if (preg_match('/^([^:]+):\s+(.*)\s*\\z/', $line, $matches)) {
                $headers[strtolower($matches[1])] = trim($matches[2]);
            } elseif (preg_match('|^HTTP/1.[01] ([0-9]{3}) |', $line, $matches)) {
                $reply = (int)$matches[1];
                if ($reply == 304 && ($lastmodified || ($lastmodified === false))) {
                    return false;
                }

                if (!in_array($reply, array(200, 301, 302, 303, 305, 307))) {
                    return PEAR::raiseError("File $scheme://$host:$port$path not valid (received: $line)");
                }
            }
            $line = array_shift($retrieved);
        }

        if ($reply != 200) {
            if (!isset($headers['location'])) {
                return PEAR::raiseError("File $scheme://$host:$port$path not valid (redirected but no location)");
            }

            if ($wasredirect > 4) {
                return PEAR::raiseError("File $scheme://$host:$port$path not valid (redirection looped more than 5 times)");
            }

            $redirect = $wasredirect + 1;
            return $this->downloadHttp($headers['location'],
                    $ui, $save_dir, $callback, $lastmodified, $accept);
        }

        $retrieved = join("\n", $retrieved);

        if (isset($headers['content-disposition']) &&
            preg_match('/\sfilename=\"([^;]*\S)\"\s*(;|\\z)/', $headers['content-disposition'], $matches)) {
            $save_as = basename($matches[1]);
        } else {
            $save_as = basename($url);
        }

        if ($callback) {
            $tmp = call_user_func($callback, 'saveas', $save_as);
            if ($tmp) {
                $save_as = $tmp;
            }
        }

        $dest_file = $save_dir . DIRECTORY_SEPARATOR . $save_as;
        if (!$wp = @fopen($dest_file, 'wb')) {
            if ($callback) {
                call_user_func($callback, 'writefailed', array($dest_file, $php_errormsg));
            }
            return PEAR::raiseError("could not open $dest_file for writing");
        }
        if (isset($headers['content-length'])) {
            $length = $headers['content-length'];
        } else {
            $length = -1;
        }
        $bytes = 0;
        if ($callback) {
            call_user_func($callback, 'start', array(basename($dest_file), $length));
        }
        $start = 0;
        while ($start < strlen($retrieved) - 1) {
            $data = substr($retrieved, $start, 1024);
            $start += 1024;
            $bytes += strlen($data);
            if ($callback) {
                call_user_func($callback, 'bytesread', $bytes);
            }
            if (!@fwrite($wp, $data)) {
                if ($callback) {
                    call_user_func($callback, 'writefailed', array($dest_file, $php_errormsg));
                }
                return PEAR::raiseError("$dest_file: write failed ($php_errormsg)");
            }
        }
        fclose($wp);
        if ($callback) {
            call_user_func($callback, 'done', $bytes);
        }
        if ($lastmodified === false || $lastmodified) {
            return array($dest_file, array('ETag' => 'blahbnlhablah', 'Last-Modified' => 'Hi'));
        }
        return $dest_file;
    }

    /**
     * For simpler unit-testing
     * @param PEAR_Config
     * @param array
     * @param array
     * @param int
     */
    function &getDependency2Object(&$c, $i, $p, $s)
    {
        $z = &test_PEAR_Dependency2::singleton($c, $i, $p, $s);
        return $z;
    }

    /**
     * For simpler unit-testing
     * @param PEAR_Downloader
     * @return PEAR_Downloader_Package
     */
    function newDownloaderPackage(&$t)
    {
        $a = new test_PEAR_Downloader_Package($t);
        return $a;
    }

    /**
     * For simpler unit-testing
     * @return string
     */
    function getDownloaderPackageClass()
    {
        return 'test_PEAR_Downloader_Package';
    }

    function log($level, $msg, $append_crlf = true)
    {
        global $fakelog;
        if (isset($fakelog)) {
            $fakelog->log($level, $msg);
        } else {
            return parent::log($level, $msg);
        }
    }
}

require_once 'PEAR/Downloader/Package.php';
class test_PEAR_Downloader_Package extends PEAR_Downloader_Package
{
    function &getDependency2Object($c, $i, $p, $s)
    {
        $a = &test_PEAR_Dependency2::singleton($c, $i, $p, $s);
        return $a;
    }

    /**
     * For simpler unit-testing
     * @param PEAR_Config
     * @param int
     * @param string
     */
    function &getPackagefileObject(&$c, $d, $dir = null)
    {
        $a = new test_PEAR_PackageFile($c, $d, $dir);
        return $a;
    }
}
require_once 'PEAR/Dependency2.php';
class test_PEAR_Dependency2 extends PEAR_Dependency2
{
    var $_fakeOS;
    var $_phpVersion;
    var $_extensions;
    var $_installerVer;

    function &singleton($config = null, $installoptions = null, $package = null,
                              $state = PEAR_VALIDATE_INSTALLING)
    {
        if (!isset($GLOBALS['_test_dep'])) {
            $GLOBALS['_test_dep'] = new test_PEAR_Dependency2($config,
                $installoptions, $package, $state);
        }
        $a = new test_PEAR_Dependency2($config, $installoptions, $package, $state);
        $a->setOS(@$GLOBALS['_test_dep']->getSysname());
        $a->setPHPVersion(@$GLOBALS['_test_dep']->phpversion());
        $a->sysname = @$GLOBALS['_test_dep']->sysname;
        $a->release = @$GLOBALS['_test_dep']->release;
        $a->cpu = @$GLOBALS['_test_dep']->cpu;
        $a->extra = @$GLOBALS['_test_dep']->extra;
        $a->nodename = @$GLOBALS['_test_dep']->nodename;
        $a->setPEARVersion(@$GLOBALS['_test_dep']->getPEARVersion());
        $a->setExtensions(@$GLOBALS['_test_dep']->_extensions);
        return $a;
    }

    function setOS($os)
    {
        $this->_fakeOS = $os;
    }

    function getPHP_OS()
    {
        return $this->_fakeOS;
    }

    function getSysname()
    {
        return $this->_fakeOS;
    }

    function setArch($uname, $extra)
    {
        $this->extra = $extra;
        list($this->sysname,
             $this->release,
             $this->cpu,
             $this->extra,
             $this->nodename) = $this->parseSignature($uname, $extra);
    }

    function setPHPversion($version)
    {
        $this->_phpVersion = $version;
    }

    function phpversion($name = null)
    {
        if ($name === null) {
            return $this->_phpVersion;
        } else {
            return $this->extversion($name);
        }
    }

    function setPEARversion($version)
    {
        $this->_installerVer = $version;
    }

    function getPEARVersion()
    {
        return $this->_installerVer;
    }

    function setExtensions($exts)
    {
        $this->_extensions = $exts;
    }

    function parseSignature($uname, $extra)
    {
        static $sysmap = array(
            'HP-UX' => 'hpux',
            'IRIX64' => 'irix',
        );
        static $cpumap = array(
            'i586' => 'i386',
            'i686' => 'i386',
            'ppc' => 'powerpc',
        );
        $parts = preg_split('/[[:space:]]+/', trim($uname));
        $n = count($parts);

        $release = $machine = $cpu = '';
        $sysname = $parts[0];
        $nodename = $parts[1];
        $cpu = $parts[$n-1];
        $extra = '';
        if ($cpu == 'unknown') {
            $cpu = $parts[$n-2];
        }

        switch ($sysname) {
            case 'AIX' :
                $release = "$parts[3].$parts[2]";
                break;
            case 'Windows' :
                switch ($parts[1]) {
                    case '95/98':
                        $release = '9x';
                        break;
                    default:
                        $release = $parts[1];
                        break;
                }
                $cpu = 'i386';
                break;
            case 'Linux' :
                $extra = $this->_detectGlibcVersion();
                // use only the first two digits from the kernel version
                $release = preg_replace('/^([0-9]+\.[0-9]+).*/', '\1', $parts[2]);
                break;
            case 'Mac' :
                $sysname = 'darwin';
                $nodename = $parts[2];
                $release = $parts[3];
                if ($cpu == 'Macintosh') {
                    if ($parts[$n - 2] == 'Power') {
                        $cpu = 'powerpc';
                    }
                }
                break;
            case 'Darwin' :
                if ($cpu == 'Macintosh') {
                    if ($parts[$n - 2] == 'Power') {
                        $cpu = 'powerpc';
                    }
                }
                $release = preg_replace('/^([0-9]+\.[0-9]+).*/', '\1', $parts[2]);
                break;
            default:
                $release = preg_replace('/-.*/', '', $parts[2]);
                break;
        }


        if (isset($sysmap[$sysname])) {
            $sysname = $sysmap[$sysname];
        } else {
            $sysname = strtolower($sysname);
        }
        if (isset($cpumap[$cpu])) {
            $cpu = $cpumap[$cpu];
        }
        return array($sysname, $release, $cpu, $extra, $nodename);
    }

    function _detectGlibcVersion()
    {
        return $this->extra;
    }

    function matchSignature($match)
    {
        if (is_array($match)) {
            $fragments = $match;
        } else {
            $fragments = explode('-', $match);
        }
        $n = count($fragments);
        $matches = 0;
        if ($n > 0) {
            $matches += $this->_matchFragment($fragments[0], $this->sysname);
        }
        if ($n > 1) {
            $matches += $this->_matchFragment($fragments[1], $this->release);
        }
        if ($n > 2) {
            $matches += $this->_matchFragment($fragments[2], $this->cpu);
        }
        if ($n > 3) {
            $matches += $this->_matchFragment($fragments[3], $this->extra);
        }
        return ($matches == $n);
    }

    function _matchFragment($fragment, $value)
    {
        if (strcspn($fragment, '*?') < strlen($fragment)) {
            $reg = '/^' . str_replace(array('*', '?', '/'), array('.*', '.', '\\/'), $fragment) . '$/i';
            return preg_match($reg, $value);
        }
        return ($fragment == '*' || !strcasecmp($fragment, $value));
    }

    function extension_loaded($name)
    {
        return isset($this->_extensions[$name]);
    }

    function extversion($ext)
    {
        if ($this->extension_loaded($ext)) {
            return $this->_extensions[$ext];
        } else {
            return false;
        }
    }
}
require_once 'PEAR/PackageFile.php';
class test_PEAR_PackageFile extends PEAR_PackageFile
{
    function getClassPrefix()
    {
        return 'test_PEAR_PackageFile_v';
    }
}
require_once 'PEAR/PackageFile/v2/rw.php';
class test_PEAR_PackageFile_v2 extends PEAR_PackageFile_v2_rw
{
    function &getPEARDownloader(&$i, $o, &$c)
    {
        $z = new test_PEAR_Downloader($i, $o, $c);
        $GLOBALS['last_dl'] = &$z;
        return $z;
    }

    function &getPEARDependency2(&$c, $o, $p, $s = PEAR_VALIDATE_INSTALLING)
    {
        $z = &test_PEAR_Dependency2::singleton($c, $o, $p, $s);
        return $z;
    }
}
require_once 'PEAR/PackageFile/v1.php';
class test_PEAR_PackageFile_v1 extends PEAR_PackageFile_v1
{
    function &getPEARDownloader(&$i, $o, &$c)
    {
        $z = new test_PEAR_Downloader($i, $o, $c);
        $GLOBALS['last_dl'] = &$z;
        return $z;
    }

    function &getPEARDependency2(&$c, $o, $p, $s = PEAR_VALIDATE_INSTALLING)
    {
        $z = &test_PEAR_Dependency2::singleton($c, $o, $p, $s);
        return $z;
    }
}

class test_PEAR_Config extends PEAR_Config
{
    function &getREST($version, $options = array())
    {
        $version = str_replace('.', '', $version);
        $class = 'test_PEAR_REST_' . $version;
        $remote = new $class($this, $options);
        return $remote;
    }

    public static function &singleton($user_file = '', $system_file = '', $strict = true)
    {
        if (is_object($GLOBALS['_PEAR_Config_instance'])) {
            return $GLOBALS['_PEAR_Config_instance'];
        }
        $GLOBALS['_PEAR_Config_instance'] =
             new test_PEAR_Config($user_file, $system_file);
        return $GLOBALS['_PEAR_Config_instance'];
    }
}

require_once 'PEAR/Frontend.php';
class fake_log extends PEAR_Frontend
{
    var $_log = array();
    var $_download = array();
    function fake_log()
    {
        $GLOBALS['_PEAR_FRONTEND_SINGLETON'] = &$GLOBALS['fakelog'];
    }

    //this is nonsense; parameters do not match with parent.
    function log($level, $message = true)
    {
        $GLOBALS['fakelog']->_log[] = array($level, $message);
    }

    function outputData($info, $cmd = 'no command')
    {
        $GLOBALS['fakelog']->_log[] = array('info' => $info, 'cmd' => $cmd);
    }

    function _downloadCallback($msg, $params)
    {
        if ($msg == 'setup') {
            $params = 'self';
        }
        $GLOBALS['fakelog']->_download[] = array($msg, $params);
    }

    function getLog()
    {
        $log = $GLOBALS['fakelog']->_log;
        $GLOBALS['fakelog']->_log = array();
        return $log;
    }

    function getDownload()
    {
        $log = $GLOBALS['fakelog']->_download;
        $GLOBALS['fakelog']->_download = array();
        return $log;
    }

    function setDialogOutput($input, $output)
    {
        $GLOBALS['fakelog']->_dialogInput[var_export($input, true)] = $output;
    }

    function userDialog($command, $prompts, $types = array(), $defaults = array())
    {
        $input = func_get_args();
        if (isset($GLOBALS['fakelog']->_dialogInput[var_export($input, true)])) {
            return $GLOBALS['fakelog']->_dialogInput[var_export($input, true)];
        }
    }
}

function cleanall($dir = null)
{
    if ($dir !== null) {
        $statedir = $dir;
    } else {
        $statedir = $GLOBALS['statedir'];
    }
    clearstatcache();
    if (file_exists($statedir)) {
        $dp = opendir($statedir);
        while ($ent = readdir($dp)) {
            if (in_array($ent, array('.', '..'))) {
                continue;
            }
            if (is_dir($statedir . DIRECTORY_SEPARATOR . $ent)) {
                cleanall($statedir . DIRECTORY_SEPARATOR . $ent);
                continue;
            }
            unlink($statedir . DIRECTORY_SEPARATOR . $ent);
        }
        closedir($dp);
        rmdir($statedir);
    }
}
