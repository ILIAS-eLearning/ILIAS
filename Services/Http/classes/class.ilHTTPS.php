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

/**
 * Class ilHTTPS
 * @author  Stefan Meyer <meyer@leifos.com>
 *
 *          Find usages: (((DIC|GLOBALS)\[['"]https.*)|(global .* $https))
 */
class ilHTTPS
{
    protected const PROTOCOL_HTTP = 1;
    protected const PROTOCOL_HTTPS = 2;
    public const SETTINGS_GROUP_SERVER = 'server';
    public const SETTING_HTTP_PATH = 'http_path';
    public const SETTINGS_GROUP_HTTPS = 'https';
    public const SETTING_AUTO_HTTPS_DETECT_ENABLED = "auto_https_detect_enabled";
    public const SETTING_AUTO_HTTPS_DETECT_HEADER_NAME = "auto_https_detect_header_name";
    public const SETTING_AUTO_HTTPS_DETECT_HEADER_VALUE = "auto_https_detect_header_value";
    public const SETTING_FORCED = 'forced';
    protected bool $enabled = false;
    protected array $protected_classes = [];
    protected array $protected_scripts = [];
    protected bool $automatic_detection = false;
    protected ?string $header_name = null;
    protected ?string $header_value = null;
    protected ilIniFile $ilias_ini;
    protected ilIniFile $client_ini;

    public function __construct()
    {
        global $DIC;
        $this->ilias_ini = $DIC->iliasIni();
        $this->client_ini = $DIC->clientIni();

        if ($this->enabled = (bool) $this->ilias_ini->readVariable(
            self::SETTINGS_GROUP_HTTPS,
            self::SETTING_FORCED
        )) {
            $this->readProtectedScripts();
            $this->readProtectedClasses();
        }

        if ($this->automatic_detection = (bool) $this->ilias_ini->readVariable(
            self::SETTINGS_GROUP_HTTPS,
            self::SETTING_AUTO_HTTPS_DETECT_ENABLED
        )) {
            $this->header_name = $this->ilias_ini->readVariable(
                self::SETTINGS_GROUP_HTTPS,
                self::SETTING_AUTO_HTTPS_DETECT_HEADER_NAME
            );
            $this->header_value = $this->ilias_ini->readVariable(
                self::SETTINGS_GROUP_HTTPS,
                self::SETTING_AUTO_HTTPS_DETECT_HEADER_VALUE
            );
        }
    }

    private function readProtectedScripts() : void
    {
        $this->protected_scripts[] = 'login.php';
        $this->protected_scripts[] = 'index.php';
        $this->protected_scripts[] = 'register.php';
        $this->protected_scripts[] = 'webdav.php';
        $this->protected_scripts[] = 'shib_login.php';
    }

    /**
     * check if https is detected
     *
     * @return bool, if https is detected by protocol or by automatic detection, if enabled, false otherwise
     */
    public function isDetected() : bool
    {
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            return true;
        }

        if ($this->automatic_detection) {
            $header_name = "HTTP_" . str_replace("-", "_", strtoupper($this->header_name));
            /* echo $header_name;
             echo $_SERVER[$header_name];*/
            if (isset($_SERVER[$header_name])) {
                if (strcasecmp($_SERVER[$header_name], $this->header_value) == 0) {
                    $_SERVER["HTTPS"] = "on";
                    return true;
                }
            }
        }

        return false;
    }

    private function readProtectedClasses() : void
    {
        $this->protected_classes[] = 'ilstartupgui';
        $this->protected_classes[] = 'ilaccountregistrationgui';
        $this->protected_classes[] = 'ilpersonalsettingsgui';
    }

    public function checkHTTPS(int $port = 443) : bool
    {
        if (($sp = fsockopen($_SERVER["SERVER_NAME"], $port, $errno, $error)) === false) {
            return false;
        }
        fclose($sp);
        return true;
    }

    public function enableSecureCookies() : void
    {
        $secure_disabled = (bool) $this->client_ini->readVariable('session', 'disable_secure_cookies');
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
    }

    public function checkProtocolAndRedirectIfNeeded() : bool
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

    private function shouldSwitchProtocol($to_protocol) : bool
    {
        switch ($to_protocol) {
            case self::PROTOCOL_HTTP:
                return (
                        !in_array(basename($_SERVER['SCRIPT_NAME']), $this->protected_scripts) &&
                        !in_array(strtolower($_GET['cmdClass']), $this->protected_classes)
                    ) && $_SERVER['HTTPS'] == 'on';

            case self::PROTOCOL_HTTPS:
                return (
                        in_array(basename($_SERVER['SCRIPT_NAME']), $this->protected_scripts) ||
                        in_array(strtolower($_GET['cmdClass']), $this->protected_classes)
                    ) && $_SERVER['HTTPS'] != 'on';
        }

        return false;
    }
}
