<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* HTTPS
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/

class ilHTTPS
{
    const PROTOCOL_HTTP = 1;
    const PROTOCOL_HTTPS = 2;
    
    private static $instance = null;

    protected $enabled = false;

    protected $protected_classes = array();
    protected $protected_scripts = array();

    protected $automaticHTTPSDetectionEnabled = false;
    protected $headerName = false;
    protected $headerValue = false;

    /**
     * @deprected use <code>ilHTTPS::getInstance()</code>
     * @return
     */
    public function __construct()
    {
        global $ilSetting, $ilIliasIniFile;

        if ($this->enabled = (bool) $ilSetting->get('https')) {
            $this->__readProtectedScripts();
            $this->__readProtectedClasses();
        }

        if ($this->automaticHTTPSDetectionEnabled = (bool) $ilIliasIniFile->readVariable('https', "auto_https_detect_enabled")) {
            $this->headerName = $ilIliasIniFile->readVariable('https', "auto_https_detect_header_name");
            $this->headerValue = $ilIliasIniFile->readVariable('https', "auto_https_detect_header_value");
        }
    }
    
    /**
     * Get https instance
     * @return
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilHTTPS();
    }

    /**
     * @param bool $to_protocol
     * @return bool
     */
    protected function shouldSwitchProtocol($to_protocol)
    {
        switch ($to_protocol) {
            case self::PROTOCOL_HTTP:
                $should_switch_to_http = (
                    !in_array(basename($_SERVER['SCRIPT_NAME']), $this->protected_scripts) &&
                    !in_array(strtolower($_GET['cmdClass']), $this->protected_classes)
                ) && $_SERVER['HTTPS'] == 'on';

                return $should_switch_to_http;
                break;

            case self::PROTOCOL_HTTPS:
                $should_switch_to_https = (
                    in_array(basename($_SERVER['SCRIPT_NAME']), $this->protected_scripts) ||
                    in_array(strtolower($_GET['cmdClass']), $this->protected_classes)
                ) && $_SERVER['HTTPS'] != 'on';

                return $should_switch_to_https;
                break;
        }

        return false;
    }

    /**
     * check if current port usage is right: if https should be used than redirection is done, to http otherwise.
     *
     * @return unknown
     */
    public function checkPort()
    {
        // if https is enabled for scripts or classes, check for redirection
        if ($this->enabled) {
            if ($this->shouldSwitchProtocol(self::PROTOCOL_HTTPS)) {
                header("location: https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                exit;
            }
            if ($this->shouldSwitchProtocol(self::PROTOCOL_HTTP)) {
                header("location: http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                exit;
            }
        }
        return true;
    }

    public function __readProtectedScripts()
    {
        $this->protected_scripts[] = 'login.php';
        $this->protected_scripts[] = 'index.php';
        $this->protected_scripts[] = 'register.php';
        $this->protected_scripts[] = 'webdav.php';
        $this->protected_scripts[] = 'shib_login.php';
        
        return true;
    }

    /**
     * check if https is detected
     *
     * @return boolean true, if https is detected by protocol or by automatic detection, if enabled, false otherwise
     */
    public function isDetected()
    {
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            return true;
        }

        if ($this->automaticHTTPSDetectionEnabled) {
            $headerName = "HTTP_" . str_replace("-", "_", strtoupper($this->headerName));
            /* echo $headerName;
             echo $_SERVER[$headerName];*/
            if (strcasecmp($_SERVER[$headerName], $this->headerValue) == 0) {
                $_SERVER["HTTPS"] = "on";
                return true;
            }
            /*
            if(isset($_SERVER[$this->headerName]) && (strcasecmp($_SERVER[$this->headerName],$this->headerValue) == 0))
            {
                $_SERVER['HTTPS'] = 'on';
                return true;
            }
            */
        }

        return false;
    }

    public function __readProtectedClasses()
    {
        $this->protected_classes[] = 'ilstartupgui';
        $this->protected_classes[] = 'ilaccountregistrationgui';
        $this->protected_classes[] = 'ilpersonalsettingsgui';
    }

    /**
    * static method to check if https connections are possible for this server
    * @access	public
    * @return	boolean
    */
    public static function _checkHTTPS()
    {
        // only check standard port in the moment
        $port = 443;

        if (($sp = fsockopen($_SERVER["SERVER_NAME"], $port, $errno, $error)) === false) {
            return false;
        }
        fclose($sp);
        return true;
    }
    /**
    * static method to check if http connections are possible for this server
    *
    * @access	public
    * @return	boolean
    */
    public function _checkHTTP()
    {
        $port = 80;

        if (($sp = fsockopen($_SERVER["SERVER_NAME"], $port, $errno, $error)) === false) {
            return false;
        }
        fclose($sp);
        return true;
    }
    
    /**
     * enable secure cookies
     *
     * @access public
     * @param
     * @return
     */
    public function enableSecureCookies()
    {
        global $ilClientIniFile;

        $secure_disabled = $ilClientIniFile->readVariable('session', 'disable_secure_cookies');
        if (!$secure_disabled && !$this->enabled && $this->isDetected() && !session_id()) {
            if (!defined('IL_COOKIE_SECURE')) {
                define('IL_COOKIE_SECURE', true);
            }

            session_set_cookie_params(
                IL_COOKIE_EXPIRE,
                IL_COOKIE_PATH,
                IL_COOKIE_DOMAIN,
                true,
                IL_COOKIE_HTTPONLY
            );
        }

        return true;
    }
}
