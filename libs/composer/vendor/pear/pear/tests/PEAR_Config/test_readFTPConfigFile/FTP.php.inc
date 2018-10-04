<?php

define('NET_FTP_ERR_OVERWRITELOCALFILE_FAILED', -14);
define('NET_FTP_ERR_DIRCHANGE_FAILED', 2); // Compatibillity reasons!
define('NET_FTP_ERR_CREATEDIR_FAILED', 1);
define('NET_FTP_ERR_OVERWRITELOCALFILE_FORBIDDEN', 2);
if (!defined('FTP_BINARY')) {
    define('FTP_BINARY', 1);
}
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
        $f->_putfiles[$local] = $remote;
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
        if ($this->_failMkdir[$dir] || (dirname($dir) != '.' &&
              $isset($f->_dirsExisting[dirname($dir)]))) {
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
?>
