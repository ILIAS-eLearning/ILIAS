<?php
if (!defined('FTP_BINARY')) {
    define('FTP_BINARY', 1);
}

define('NET_FTP_ERR_OVERWRITELOCALFILE_FAILED', -14);
define('NET_FTP_ERR_DIRCHANGE_FAILED', 2); // Compatibillity reasons!
define('NET_FTP_ERR_CREATEDIR_FAILED', 1);
define('NET_FTP_ERR_OVERWRITELOCALFILE_FORBIDDEN', 32);
define('NET_FTP_ERR_UPLOADFILE_FAILED', 5);
function Net_FTP_setup(&$t)
{
    $a = &Net_FTP::singleton();
    foreach (get_object_vars($a) as $name => $val) {
        $t->$name = $val;
    }
}
/**
 * mock object
 */
class Net_FTP extends PEAR
{
    var $_connectErr = false;
    var $_loginErr = false;
    var $_cdErr = false;
    var $_cdTo = array();
    var $_dirsExisting = array();
    var $_dirsMade = array();
    var $_rmfiles = array();
    var $_host;
    var $_port;
    var $_handle;
    var $_failMkdir = array();
    function Net_FTP($host = true, $port = false, $timeout = false)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_timeout = $timeout;
        if ($host) {
            Net_FTP_setup($this);
        }
    }

    /**
     * @return Net_FTP
     */
    function &singleton()
    {
        if (!isset($GLOBALS['_TEST_FTP'])) {
            $GLOBALS['_TEST_FTP'] = new Net_FTP(false, false, false);
        }
        return $GLOBALS['_TEST_FTP'];
    }

    function connect()
    {
        if ($this->_connectErr) {
            return PEAR::raiseError($this->_connectErr);
        }
    }

    function setConnectError($err)
    {
        $this->_connectErr = $err;
    }

    function login($user, $pass)
    {
        if ($this->_loginErr) {
            return $this->raiseError($this->_loginErr);
        }
    }

    function setLoginError($err)
    {
        $this->_loginErr = $err;
    }

    function cd($path)
    {
        $f = &Net_FTP::singleton();
        if (isset($this->_cdErr[$path])) {
            return $this->raiseError($this->_cdErr[$path]);
        }
        if ($path == '/' || $path == '\\') {
           $path = '';
        }
        if ($path != '' && !isset($f->_dirsExisting[$path])) {
            return $this->raiseError("Directory change failed", NET_FTP_ERR_DIRCHANGE_FAILED);
        }
        $this->_cdTo = $path;
        return true;
    }

    function setCdError($err)
    {
        $this->_cdErr = $err;
    }

    function put($local, $remote)
    {
        $f = &Net_FTP::singleton();
        if (!isset($f->_dirsExisting[dirname($remote)])) {
            return $this->raiseError("File '$local_file' could not be uploaded to '$remote_file'.", NET_FTP_ERR_UPLOADFILE_FAILED);
        }
        $f->_putfiles[$local] = $remote;
        return true;
    }

    function rm($remote)
    {
        $f = &Net_FTP::singleton();
        $f->_rmfiles[] = $remote;
        return true;
    }

    function pwd()
    {
        return $this->_cdTo;
    }

    function _construct_path($path)
    {
        return $path;
    }

    function _testftp_mkdir($handle, $dir)
    {
        $f = &Net_FTP::singleton();
        if (isset($this->_failMkdir[$dir]) || (dirname($dir) != '.' &&
              isset($f->_dirsExisting[$dir]))) {
            return false;
        }
        $f->_dirsMade[$dir] = true;
        $f->_dirsExisting[$dir] = true;
        return true;
    }

    function setFailmkdir($dirs)
    {
        $this->_failMkdir = $dirs;
    }

    function get($remote, $local, $overwrite = false, $mode = null)
    {
        if ($this->_cdTo) {
            $remote = $this->_cdTo . '/' . $remote;
        }
        if (isset($this->_remoteFiles[$remote])) {
            if (!$overwrite && file_exists($local)) {
                return $this->raiseError("Local file '$local_file' exists and may not be overwriten.", NET_FTP_ERR_OVERWRITELOCALFILE_FORBIDDEN);
            }
            $contents = implode('', file($this->_remoteFiles[$remote]));
            $fp = fopen($local, 'wb');
            fwrite($fp, $contents);
            fclose($fp);
            return true;
        }
        return $this->raiseError("File '$remote' could not be downloaded to 'local'.", NET_FTP_ERR_OVERWRITELOCALFILE_FAILED);
    }

    function addRemoteFile($path, $loc)
    {
        $this->_remoteFiles[$path] = $loc;
    }

    function disconnect()
    {
    }

    function setDirsExisting($dirs)
    {
        $f = &Net_FTP::singleton();
        $f->_dirsExisting = array_flip($dirs);
    }
}
require_once 'PEAR/RemoteInstaller.php';
class test_PEAR_RemoteInstaller extends PEAR_RemoteInstaller {
    function download($packages, $options, &$config, &$installpackages,
                      &$errors, $installed = false, $willinstall = false, $state = false)
    {
        // trickiness: initialize here
        $this->PEAR_Downloader($this->ui, $options, $config);
        $this->_remote = new test_PEAR_Remote($config);
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
    
    function downloadHttp($url, &$ui, $save_dir = '.', $callback = null)
    {
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
        $retrieved = explode("\n", $this->_remote->_pearweb->receiveHttp($url));
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

    function log($level, $msg)
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
?>
