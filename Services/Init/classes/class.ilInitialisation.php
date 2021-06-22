<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

// TODO:
use ILIAS\BackgroundTasks\Dependencies\DependencyMap\BaseDependencyMap;
use ILIAS\BackgroundTasks\Implementation\Persistence\BasicPersistence;
use ILIAS\Filesystem\Provider\FilesystemFactory;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizerImpl;
use ILIAS\FileUpload\Processor\BlacklistExtensionPreProcessor;
use ILIAS\FileUpload\Processor\FilenameSanitizerPreProcessor;
use ILIAS\FileUpload\Processor\PreProcessorManagerImpl;
use ILIAS\FileUpload\Processor\VirusScannerPreProcessor;
use ILIAS\GlobalScreen\Services;

require_once("libs/composer/vendor/autoload.php");

// needed for slow queries, etc.
if (!isset($GLOBALS['ilGlobalStartTime']) || !$GLOBALS['ilGlobalStartTime']) {
    $GLOBALS['ilGlobalStartTime'] = microtime();
}

include_once "Services/Context/classes/class.ilContext.php";

/** @defgroup ServicesInit Services/Init
 */

/**
* ILIAS Initialisation Utility Class
* perform basic setup: init database handler, load configuration file,
* init user authentification & error handler, load object type definitions
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Sascha Hofmann <shofmann@databay.de>

* @version $Id$
*
* @ingroup ServicesInit
*/
class ilInitialisation
{
    /**
     * Remove unsafe characters from GET
     */
    protected static function removeUnsafeCharacters()
    {
        // Remove unsafe characters from GET parameters.
        // We do not need this characters in any case, so it is
        // feasible to filter them everytime. POST parameters
        // need attention through ilUtil::stripSlashes() and similar functions)
        $_GET = self::recursivelyRemoveUnsafeCharacters($_GET);
    }

    protected static function recursivelyRemoveUnsafeCharacters($var)
    {
        if (is_array($var)) {
            $mod = [];
            foreach ($var as $k => $v) {
                $k = self::recursivelyRemoveUnsafeCharacters($k);
                $mod[$k] = self::recursivelyRemoveUnsafeCharacters($v);
            }
            return $mod;
        }
        return strip_tags(
            str_replace(
                array("\x00", "\n", "\r", "\\", "'", '"', "\x1a"),
                "",
                $var
            )
        );
    }
    
    /**
     * get common include code files
     */
    protected static function requireCommonIncludes()
    {
        // ilTemplate
        if (ilContext::usesTemplate()) {
            require_once "./Services/UICore/classes/class.ilTemplate.php";
        }
                
        // really always required?
        require_once "./Services/Utilities/classes/class.ilUtil.php";
        require_once "./Services/Calendar/classes/class.ilDatePresentation.php";
        require_once "include/inc.ilias_version.php";
        
        include_once './Services/Authentication/classes/class.ilAuthUtils.php';
        
        self::initGlobal("ilBench", "ilBenchmark", "./Services/Utilities/classes/class.ilBenchmark.php");
    }
    
    /**
     * This is a hack for  authentication.
     *
     * Since the phpCAS lib ships with its own compliance functions.
     */
    protected static function includePhp5Compliance()
    {
        include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
        if (ilAuthFactory::getContext() != ilAuthFactory::CONTEXT_CAS) {
            require_once("include/inc.xml5compliance.php");
        }
        require_once("include/inc.xsl5compliance.php");
    }

    /**
     * This method provides a global instance of class ilIniFile for the
     * ilias.ini.php file in variable $ilIliasIniFile.
     *
     * It initializes a lot of constants accordingly to the settings in
     * the ilias.ini.php file.
     */
    protected static function initIliasIniFile()
    {
        require_once("./Services/Init/classes/class.ilIniFile.php");
        $ilIliasIniFile = new ilIniFile("./ilias.ini.php");
        $ilIliasIniFile->read();
        self::initGlobal('ilIliasIniFile', $ilIliasIniFile);

        // initialize constants
        define("ILIAS_DATA_DIR", $ilIliasIniFile->readVariable("clients", "datadir"));
        define("ILIAS_WEB_DIR", $ilIliasIniFile->readVariable("clients", "path"));
        define("ILIAS_ABSOLUTE_PATH", $ilIliasIniFile->readVariable('server', 'absolute_path'));

        // logging
        define("ILIAS_LOG_DIR", $ilIliasIniFile->readVariable("log", "path"));
        define("ILIAS_LOG_FILE", $ilIliasIniFile->readVariable("log", "file"));
        define("ILIAS_LOG_ENABLED", $ilIliasIniFile->readVariable("log", "enabled"));
        define("ILIAS_LOG_LEVEL", $ilIliasIniFile->readVariable("log", "level"));
        define("SLOW_REQUEST_TIME", $ilIliasIniFile->readVariable("log", "slow_request_time"));

        // read path + command for third party tools from ilias.ini
        define("PATH_TO_CONVERT", $ilIliasIniFile->readVariable("tools", "convert"));
        define("PATH_TO_FFMPEG", $ilIliasIniFile->readVariable("tools", "ffmpeg"));
        define("PATH_TO_ZIP", $ilIliasIniFile->readVariable("tools", "zip"));
        define("PATH_TO_MKISOFS", $ilIliasIniFile->readVariable("tools", "mkisofs"));
        define("PATH_TO_UNZIP", $ilIliasIniFile->readVariable("tools", "unzip"));
        define("PATH_TO_GHOSTSCRIPT", $ilIliasIniFile->readVariable("tools", "ghostscript"));
        define("PATH_TO_JAVA", $ilIliasIniFile->readVariable("tools", "java"));
        define("URL_TO_LATEX", $ilIliasIniFile->readVariable("tools", "latex"));
        define("PATH_TO_FOP", $ilIliasIniFile->readVariable("tools", "fop"));
        define("PATH_TO_LESSC", $ilIliasIniFile->readVariable("tools", "lessc"));
        define("PATH_TO_PHANTOMJS", $ilIliasIniFile->readVariable("tools", "phantomjs"));

        if ($ilIliasIniFile->groupExists('error')) {
            if ($ilIliasIniFile->variableExists('error', 'editor_url')) {
                define("ERROR_EDITOR_URL", $ilIliasIniFile->readVariable('error', 'editor_url'));
            }

            if ($ilIliasIniFile->variableExists('error', 'editor_path_translations')) {
                define("ERROR_EDITOR_PATH_TRANSLATIONS", $ilIliasIniFile->readVariable('error', 'editor_path_translations'));
            }
        }

        // read virus scanner settings
        switch ($ilIliasIniFile->readVariable("tools", "vscantype")) {
            case "sophos":
                define("IL_VIRUS_SCANNER", "Sophos");
                define("IL_VIRUS_SCAN_COMMAND", $ilIliasIniFile->readVariable("tools", "scancommand"));
                define("IL_VIRUS_CLEAN_COMMAND", $ilIliasIniFile->readVariable("tools", "cleancommand"));
                break;

            case "antivir":
                define("IL_VIRUS_SCANNER", "AntiVir");
                define("IL_VIRUS_SCAN_COMMAND", $ilIliasIniFile->readVariable("tools", "scancommand"));
                define("IL_VIRUS_CLEAN_COMMAND", $ilIliasIniFile->readVariable("tools", "cleancommand"));
                break;

            case "clamav":
                define("IL_VIRUS_SCANNER", "ClamAV");
                define("IL_VIRUS_SCAN_COMMAND", $ilIliasIniFile->readVariable("tools", "scancommand"));
                define("IL_VIRUS_CLEAN_COMMAND", $ilIliasIniFile->readVariable("tools", "cleancommand"));
                break;

            default:
                define("IL_VIRUS_SCANNER", "None");
                break;
        }

        include_once './Services/Calendar/classes/class.ilTimeZone.php';
        $tz = ilTimeZone::initDefaultTimeZone($ilIliasIniFile);
        define("IL_TIMEZONE", $tz);
    }

    /**
     * Bootstraps the ILIAS filesystem abstraction.
     * The bootstrapped abstraction are:
     *  - temp
     *  - web
     *  - storage
     *  - customizing
     *
     * @return void
     * @since 5.3
     */
    public static function bootstrapFilesystems()
    {
        global $DIC;

        $DIC['filesystem.security.sanitizing.filename'] = function ($c) {
            return new FilenameSanitizerImpl();
        };

        $DIC['filesystem.factory'] = function ($c) {
            return new \ILIAS\Filesystem\Provider\DelegatingFilesystemFactory($c['filesystem.security.sanitizing.filename']);
        };

        $DIC['filesystem.web'] = function ($c) {
            //web

            /**
             * @var FilesystemFactory $delegatingFactory
             */
            $delegatingFactory = $c['filesystem.factory'];
            $webConfiguration = new \ILIAS\Filesystem\Provider\Configuration\LocalConfig(ILIAS_ABSOLUTE_PATH . '/' . ILIAS_WEB_DIR . '/' . CLIENT_ID);
            return $delegatingFactory->getLocal($webConfiguration);
        };

        $DIC['filesystem.storage'] = function ($c) {
            //storage

            /**
             * @var FilesystemFactory $delegatingFactory
             */
            $delegatingFactory = $c['filesystem.factory'];
            $storageConfiguration = new \ILIAS\Filesystem\Provider\Configuration\LocalConfig(ILIAS_DATA_DIR . '/' . CLIENT_ID);
            return $delegatingFactory->getLocal($storageConfiguration);
        };

        $DIC['filesystem.temp'] = function ($c) {
            //temp

            /**
             * @var FilesystemFactory $delegatingFactory
             */
            $delegatingFactory = $c['filesystem.factory'];
            $tempConfiguration = new \ILIAS\Filesystem\Provider\Configuration\LocalConfig(ILIAS_DATA_DIR . '/' . CLIENT_ID . '/temp');
            return $delegatingFactory->getLocal($tempConfiguration);
        };

        $DIC['filesystem.customizing'] = function ($c) {
            //customizing

            /**
             * @var FilesystemFactory $delegatingFactory
             */
            $delegatingFactory = $c['filesystem.factory'];
            $customizingConfiguration = new \ILIAS\Filesystem\Provider\Configuration\LocalConfig(ILIAS_ABSOLUTE_PATH . '/' . 'Customizing');
            return $delegatingFactory->getLocal($customizingConfiguration);
        };

        $DIC['filesystem.libs'] = function ($c) {
            //customizing

            /**
             * @var FilesystemFactory $delegatingFactory
             */
            $delegatingFactory = $c['filesystem.factory'];
            $customizingConfiguration = new \ILIAS\Filesystem\Provider\Configuration\LocalConfig(ILIAS_ABSOLUTE_PATH . '/' . 'libs');
            return $delegatingFactory->getLocal($customizingConfiguration, true);
        };

        $DIC['filesystem'] = function ($c) {
            return new \ILIAS\Filesystem\FilesystemsImpl(
                $c['filesystem.storage'],
                $c['filesystem.web'],
                $c['filesystem.temp'],
                $c['filesystem.customizing'],
                $c['filesystem.libs']
            );
        };
    }


    /**
     * Initializes the file upload service.
     * This service requires the http and filesystem service.
     *
     * @param \ILIAS\DI\Container $dic The dependency container which should be used to load the file upload service.
     *
     * @return void
     */
    public static function initFileUploadService(\ILIAS\DI\Container $dic)
    {
        $dic['upload.processor-manager'] = function ($c) {
            return new PreProcessorManagerImpl();
        };

        $dic['upload'] = function (\ILIAS\DI\Container $c) {
            $fileUploadImpl = new \ILIAS\FileUpload\FileUploadImpl($c['upload.processor-manager'], $c['filesystem'], $c['http']);
            if (IL_VIRUS_SCANNER != "None") {
                $fileUploadImpl->register(new VirusScannerPreProcessor(ilVirusScannerFactory::_getInstance()));
            }

            $fileUploadImpl->register(new FilenameSanitizerPreProcessor());
            $fileUploadImpl->register(new BlacklistExtensionPreProcessor(ilFileUtils::getExplicitlyBlockedFiles(), $c->language()->txt("msg_info_blacklisted")));

            return $fileUploadImpl;
        };
    }

    /**
     * builds http path
     */
    protected static function buildHTTPPath()
    {
        include_once './Services/Http/classes/class.ilHTTPS.php';
        $https = new ilHTTPS();

        if ($https->isDetected()) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        $host = $_SERVER['HTTP_HOST'];

        $rq_uri = strip_tags($_SERVER['REQUEST_URI']);

        // security fix: this failed, if the URI contained "?" and following "/"
        // -> we remove everything after "?"
        if (is_int($pos = strpos($rq_uri, "?"))) {
            $rq_uri = substr($rq_uri, 0, $pos);
        }

        if (!defined('ILIAS_MODULE')) {
            $path = pathinfo($rq_uri);
            if (!$path['extension']) {
                $uri = $rq_uri;
            } else {
                $uri = dirname($rq_uri);
            }
        } else {
            // if in module remove module name from HTTP_PATH
            $path = dirname($rq_uri);

            // dirname cuts the last directory from a directory path e.g content/classes return content
            $module = ilUtil::removeTrailingPathSeparators(ILIAS_MODULE);

            $dirs = explode('/', $module);
            $uri = $path;
            foreach ($dirs as $dir) {
                $uri = dirname($uri);
            }
        }

        $iliasHttpPath = implode('', [$protocol, $host, $uri]);
        if (ilContext::getType() == ilContext::CONTEXT_APACHE_SSO) {
            $iliasHttpPath = dirname($iliasHttpPath);
        } elseif (ilContext::getType() === ilContext::CONTEXT_SAML) {
            if (strpos($iliasHttpPath, '/Services/Saml/lib/') !== false && strpos($iliasHttpPath, '/metadata.php') === false) {
                $iliasHttpPath = substr($iliasHttpPath, 0, strpos($iliasHttpPath, '/Services/Saml/lib/'));
            }
        }

        $f = new \ILIAS\Data\Factory();
        $uri = $f->uri(ilUtil::removeTrailingPathSeparators($iliasHttpPath));

        return define('ILIAS_HTTP_PATH', $uri->getBaseURI());
    }

    /**
     * This method determines the current client and sets the
     * constant CLIENT_ID.
     */
    protected static function determineClient()
    {
        global $ilIliasIniFile;

        // check whether ini file object exists
        if (!is_object($ilIliasIniFile)) {
            self::abortAndDie('Fatal Error: ilInitialisation::determineClient called without initialisation of ILIAS ini file object.');
        }

        if (isset($_GET['client_id']) && strlen($_GET['client_id']) > 0) {
            $_GET['client_id'] = \ilUtil::getClientIdByString((string) $_GET['client_id'])->toString();
            if (!defined('IL_PHPUNIT_TEST')) {
                if (ilContext::supportsPersistentSessions()) {
                    ilUtil::setCookie('ilClientId', $_GET['client_id']);
                }
            }
        } elseif (!isset($_COOKIE['ilClientId'])) {
            ilUtil::setCookie('ilClientId', $ilIliasIniFile->readVariable('clients', 'default'));
        }

        if (!defined('IL_PHPUNIT_TEST') && ilContext::supportsPersistentSessions()) {
            $clientId = $_COOKIE['ilClientId'];
        } else {
            $clientId = $_GET['client_id'];
        }

        define('CLIENT_ID', \ilUtil::getClientIdByString((string) $clientId)->toString());
    }

    /**
     * This method provides a global instance of class ilIniFile for the
     * client.ini.php file in variable $ilClientIniFile.
     *
     * It initializes a lot of constants accordingly to the settings in
     * the client.ini.php file.
     *
     * Preconditions: ILIAS_WEB_DIR and CLIENT_ID must be set.
     *
     * @return	boolean		true, if no error occured with client init file
     *						otherwise false
     */
    protected static function initClientIniFile()
    {
        global $ilIliasIniFile;

        // check whether ILIAS_WEB_DIR is set.
        if (ILIAS_WEB_DIR == "") {
            self::abortAndDie("Fatal Error: ilInitialisation::initClientIniFile called without ILIAS_WEB_DIR.");
        }

        // check whether CLIENT_ID is set.
        if (CLIENT_ID == "") {
            self::abortAndDie("Fatal Error: ilInitialisation::initClientIniFile called without CLIENT_ID.");
        }

        $ini_file = "./" . ILIAS_WEB_DIR . "/" . CLIENT_ID . "/client.ini.php";

        // get settings from ini file
        $ilClientIniFile = new ilIniFile($ini_file);
        $ilClientIniFile->read();

        // invalid client id / client ini
        if ($ilClientIniFile->ERROR != "") {
            $c = $_COOKIE["ilClientId"];
            $default_client = $ilIliasIniFile->readVariable("clients", "default");
            ilUtil::setCookie("ilClientId", $default_client);
            if (CLIENT_ID != "" && CLIENT_ID != $default_client) {
                $mess = array("en" => "Client does not exist.",
                        "de" => "Mandant ist ungültig.");
                self::redirect("index.php?client_id=" . $default_client, null, $mess);
            } else {
                self::abortAndDie("Fatal Error: ilInitialisation::initClientIniFile initializing client ini file abborted with: " . $ilClientIniFile->ERROR);
            }
        }

        self::initGlobal("ilClientIniFile", $ilClientIniFile);

        // set constants
        define("SESSION_REMINDER_LEADTIME", 30);
        define("DEBUG", $ilClientIniFile->readVariable("system", "DEBUG"));
        define("DEVMODE", $ilClientIniFile->readVariable("system", "DEVMODE"));
        define("SHOWNOTICES", $ilClientIniFile->readVariable("system", "SHOWNOTICES"));
        define("DEBUGTOOLS", $ilClientIniFile->readVariable("system", "DEBUGTOOLS"));
        define("ROOT_FOLDER_ID", $ilClientIniFile->readVariable('system', 'ROOT_FOLDER_ID'));
        define("SYSTEM_FOLDER_ID", $ilClientIniFile->readVariable('system', 'SYSTEM_FOLDER_ID'));
        define("ROLE_FOLDER_ID", $ilClientIniFile->readVariable('system', 'ROLE_FOLDER_ID'));
        define("MAIL_SETTINGS_ID", $ilClientIniFile->readVariable('system', 'MAIL_SETTINGS_ID'));
        $error_handler = $ilClientIniFile->readVariable('system', 'ERROR_HANDLER');
        define("ERROR_HANDLER", $error_handler ? $error_handler : "PRETTY_PAGE");

        // this is for the online help installation, which sets OH_REF_ID to the
        // ref id of the online module
        define("OH_REF_ID", $ilClientIniFile->readVariable("system", "OH_REF_ID"));

        define("SYSTEM_MAIL_ADDRESS", $ilClientIniFile->readVariable('system', 'MAIL_SENT_ADDRESS')); // Change SS
        define("MAIL_REPLY_WARNING", $ilClientIniFile->readVariable('system', 'MAIL_REPLY_WARNING')); // Change SS

        // see ilObject::TITLE_LENGTH, ilObject::DESC_LENGTH
        // define ("MAXLENGTH_OBJ_TITLE",125);#$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_TITLE'));
        // define ("MAXLENGTH_OBJ_DESC",$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_DESC'));

        define("CLIENT_DATA_DIR", ILIAS_DATA_DIR . "/" . CLIENT_ID);
        define("CLIENT_WEB_DIR", ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . CLIENT_ID);
        define("CLIENT_NAME", $ilClientIniFile->readVariable('client', 'name')); // Change SS

        $val = $ilClientIniFile->readVariable("db", "type");
        if ($val == "") {
            define("IL_DB_TYPE", "mysql");
        } else {
            define("IL_DB_TYPE", $val);
        }

        $ilGlobalCacheSettings = new ilGlobalCacheSettings();
        $ilGlobalCacheSettings->readFromIniFile($ilClientIniFile);
        ilGlobalCache::setup($ilGlobalCacheSettings);

        return true;
    }

    /**
     * handle maintenance mode
     */
    protected static function handleMaintenanceMode()
    {
        global $ilClientIniFile;

        if (!$ilClientIniFile->readVariable("client", "access")) {
            $mess = array(
                "en" => "The server is not available due to maintenance." .
                    " We apologise for any inconvenience.",
                "de" => "Der Server ist aufgrund von Wartungsarbeiten nicht verfügbar." .
                    " Wir bitten um Verständnis."
            );
            $mess_id = "init_error_maintenance";

            if (ilContext::hasHTML() && is_file("./maintenance.html")) {
                self::redirect("./maintenance.html", $mess_id, $mess);
            } else {
                $mess = self::translateMessage($mess_id, $mess);
                self::abortAndDie($mess);
            }
        }
    }

    /**
    * initialise database object $ilDB
    *
    */
    protected static function initDatabase()
    {
        // build dsn of database connection and connect
        $ilDB = ilDBWrapperFactory::getWrapper(IL_DB_TYPE);
        $ilDB->initFromIniFile();
        $ilDB->connect();

        self::initGlobal("ilDB", $ilDB);
    }

    /**
     * set session handler to db
     *
     * Used in Soap/CAS
     */
    public static function setSessionHandler()
    {
        if (ini_get('session.save_handler') != 'user' && version_compare(PHP_VERSION, '7.2.0', '<')) {
            ini_set("session.save_handler", "user");
        }

        require_once "Services/Authentication/classes/class.ilSessionDBHandler.php";
        $db_session_handler = new ilSessionDBHandler();
        if (!$db_session_handler->setSaveHandler()) {
            self::abortAndDie("Please turn off Safe mode OR set session.save_handler to \"user\" in your php.ini");
        }

        // Do not accept external session ids
        if (!ilSession::_exists(session_id()) && !defined('IL_PHPUNIT_TEST')) {
            // php7-todo, correct-with-php5-removal : alex, 1.3.2016: added if, please check
            if (function_exists("session_status") && session_status() == PHP_SESSION_ACTIVE) {
                session_regenerate_id();
            }
        }
    }

    /**
     *
     */
    protected static function setCookieConstants()
    {
        include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
        if (ilAuthFactory::getContext() == ilAuthFactory::CONTEXT_HTTP) {
            $cookie_path = '/';
        } elseif ($GLOBALS['COOKIE_PATH']) {
            // use a predefined cookie path from WebAccessChecker
            $cookie_path = $GLOBALS['COOKIE_PATH'];
        } else {
            $cookie_path = dirname($_SERVER['PHP_SELF']);
        }

        /* if ilias is called directly within the docroot $cookie_path
        is set to '/' expecting on servers running under windows..
        here it is set to '\'.
        in both cases a further '/' won't be appended due to the following regex
        */
        $cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

        if ($cookie_path == "\\") {
            $cookie_path = '/';
        }

        define('IL_COOKIE_HTTPONLY', true); // Default Value
        define('IL_COOKIE_EXPIRE', 0);
        define('IL_COOKIE_PATH', $cookie_path);
        define('IL_COOKIE_DOMAIN', '');
    }

    /**
     * set session cookie params
     */
    protected static function setSessionCookieParams()
    {
        global $ilSetting;

        if (!defined('IL_COOKIE_SECURE')) {
            // If this code is executed, we can assume that \ilHTTPS::enableSecureCookies was NOT called before
            // \ilHTTPS::enableSecureCookies already executes session_set_cookie_params()

            include_once './Services/Http/classes/class.ilHTTPS.php';
            $cookie_secure = !$ilSetting->get('https', 0) && ilHTTPS::getInstance()->isDetected();
            define('IL_COOKIE_SECURE', $cookie_secure); // Default Value

            session_set_cookie_params(
                IL_COOKIE_EXPIRE,
                IL_COOKIE_PATH,
                IL_COOKIE_DOMAIN,
                IL_COOKIE_SECURE,
                IL_COOKIE_HTTPONLY
            );
        }
    }

    /**
     * @param \ILIAS\DI\Container $c
     */
    protected static function initMail(\ILIAS\DI\Container $c)
    {
        $c["mail.mime.transport.factory"] = function (\ILIAS\DI\Container $c) {
            return new \ilMailMimeTransportFactory($c->settings(), $c->event());
        };
        $c["mail.mime.sender.factory"] = function (\ILIAS\DI\Container $c) {
            return new \ilMailMimeSenderFactory($c->settings());
        };
        $c["mail.texttemplates.service"] = function (\ILIAS\DI\Container $c) {
            return new \ilMailTemplateService(new \ilMailTemplateRepository($c->database()));
        };
    }

    /**
     * @param \ILIAS\DI\Container $c
     */
    protected static function initCustomObjectIcons(\ILIAS\DI\Container $c)
    {
        $c["object.customicons.factory"] = function ($c) {
            require_once 'Services/Object/Icon/classes/class.ilObjectCustomIconFactory.php';
            return new ilObjectCustomIconFactory(
                $c->filesystem()->web(),
                $c->upload(),
                $c['ilObjDataCache']
            );
        };
    }

    /**
     * @param \ILIAS\DI\Container $c
     */
    protected static function initAvatar(\ILIAS\DI\Container $c)
    {
        $c["user.avatar.factory"] = function ($c) {
            return new \ilUserAvatarFactory($c);
        };
    }

    /**
     * @param \ILIAS\DI\Container $c
     */
    protected static function initTermsOfService(\ILIAS\DI\Container $c)
    {
        $c['tos.criteria.type.factory'] = function (\ILIAS\DI\Container $c) {
            return new ilTermsOfServiceCriterionTypeFactory($c->rbac()->review(), $c['ilObjDataCache']);
        };

        $c['tos.document.evaluator'] = function (\ILIAS\DI\Container $c) {
            return new ilTermsOfServiceSequentialDocumentEvaluation(
                new ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation(
                    $c['tos.criteria.type.factory'],
                    $c->user(),
                    $c->logger()->tos()
                ),
                $c->user(),
                $c->logger()->tos(),
                \ilTermsOfServiceDocument::orderBy('sorting')->get()
            );
        };
    }

    protected static function initAccessibilityControlConcept(\ILIAS\DI\Container $c)
    {
        $c['acc.criteria.type.factory'] = function (\ILIAS\DI\Container $c) {
            return new ilAccessibilityCriterionTypeFactory($c->rbac()->review(), $c['ilObjDataCache']);
        };

        $c['acc.document.evaluator'] = function (\ILIAS\DI\Container $c) {
            return new ilAccessibilitySequentialDocumentEvaluation(
                new ilAccessibilityLogicalAndDocumentCriteriaEvaluation(
                    $c['acc.criteria.type.factory'],
                    $c->user(),
                    $c->logger()->acc()
                ),
                $c->user(),
                $c->logger()->acc(),
                \ilAccessibilityDocument::orderBy('sorting')->get()
            );
        };
    }

    /**
     * initialise $ilSettings object and define constants
     *
     * Used in Soap
     */
    protected static function initSettings()
    {
        global $ilSetting;

        self::initGlobal(
            "ilSetting",
            "ilSetting",
            "Services/Administration/classes/class.ilSetting.php"
        );

        // check correct setup
        if (!$ilSetting->get("setup_ok")) {
            self::abortAndDie("Setup is not completed. Please run setup routine again.");
        }

        // set anonymous user & role id and system role id
        define("ANONYMOUS_USER_ID", $ilSetting->get("anonymous_user_id"));
        define("ANONYMOUS_ROLE_ID", $ilSetting->get("anonymous_role_id"));
        define("SYSTEM_USER_ID", $ilSetting->get("system_user_id"));
        define("SYSTEM_ROLE_ID", $ilSetting->get("system_role_id"));
        define("USER_FOLDER_ID", 7);

        // recovery folder
        define("RECOVERY_FOLDER_ID", $ilSetting->get("recovery_folder_id"));

        // installation id
        define("IL_INST_ID", $ilSetting->get("inst_id", 0));

        // define default suffix replacements
        define("SUFFIX_REPL_DEFAULT", "php,php3,php4,inc,lang,phtml,htaccess");
        define("SUFFIX_REPL_ADDITIONAL", $ilSetting->get("suffix_repl_additional"));

        if (ilContext::usesHTTP()) {
            self::buildHTTPPath();
        }
    }

    /**
     * provide $styleDefinition object
     */
    protected static function initStyle()
    {
        global $DIC, $ilPluginAdmin;

        // load style definitions
        self::initGlobal(
            "styleDefinition",
            "ilStyleDefinition",
            "./Services/Style/System/classes/class.ilStyleDefinition.php"
        );

        // add user interface hook for style initialisation
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        foreach ($pl_names as $pl) {
            $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            $gui_class = $ui_plugin->getUIClassInstance();
            $gui_class->modifyGUI("Services/Init", "init_style", array("styleDefinition" => $DIC->systemStyle()));
        }
    }

    /**
     * Init user with current account id
     */
    public static function initUserAccount()
    {
        global $DIC;

        static $context_init;

        $uid = $GLOBALS['DIC']['ilAuthSession']->getUserId();
        if ($uid) {
            $DIC->user()->setId($uid);
            $DIC->user()->read();
            if (!isset($context_init)) {
                if ($DIC->user()->isAnonymous()) {
                    $DIC->globalScreen()->tool()->context()->claim()->external();
                } else {
                    $DIC->globalScreen()->tool()->context()->claim()->internal();
                }
                $context_init = true;
            }
            // init console log handler
            ilLoggerFactory::getInstance()->initUser($DIC->user()->getLogin());
            \ilOnlineTracking::updateAccess($DIC->user());
        } else {
            if (is_object($GLOBALS['ilLog'])) {
                $GLOBALS['ilLog']->logStack();
            }
            self::abortAndDie("Init user account failed");
        }
    }

    /**
     * Init Locale
     */
    protected static function initLocale()
    {
        global $ilSetting;

        if (trim($ilSetting->get("locale") != "")) {
            $larr = explode(",", trim($ilSetting->get("locale")));
            $ls = array();
            $first = $larr[0];
            foreach ($larr as $l) {
                if (trim($l) != "") {
                    $ls[] = $l;
                }
            }
            if (count($ls) > 0) {
                setlocale(LC_ALL, $ls);

                // #15347 - making sure that floats are not changed
                setlocale(LC_NUMERIC, "C");

                if (class_exists("Collator")) {
                    $GLOBALS["ilCollator"] = new Collator($first);
                    $GLOBALS["DIC"]["ilCollator"] = function ($c) {
                        return $GLOBALS["ilCollator"];
                    };
                }
            }
        }
    }

    /**
     * go to public section
     *
     * @param int $a_auth_stat
     */
    public static function goToPublicSection()
    {
        global $ilAuth;

        if (ANONYMOUS_USER_ID == "") {
            self::abortAndDie("Public Section enabled, but no Anonymous user found.");
        }

        $session_destroyed = false;
        if ($GLOBALS['DIC']['ilAuthSession']->isExpired()) {
            $session_destroyed = true;
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_EXPIRE);
        }
        if (!$GLOBALS['DIC']['ilAuthSession']->isAuthenticated()) {
            $session_destroyed = true;
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_PUBLIC);
        }

        if ($session_destroyed) {
            $GLOBALS['DIC']['ilAuthSession']->setAuthenticated(true, ANONYMOUS_USER_ID);
        }

        self::initUserAccount();

        // if target given, try to go there
        if (strlen($_GET["target"])) {
            // when we are already "inside" goto.php no redirect is needed
            $current_script = substr(strrchr($_SERVER["PHP_SELF"], "/"), 1);
            if ($current_script == "goto.php") {
                return;
            }
            // goto will check if target is accessible or redirect to login
            self::redirect("goto.php?target=" . $_GET["target"]);
        }

        // check access of root folder otherwise redirect to login
        #if(!$GLOBALS['DIC']->rbac()->system()->checkAccess('read', ROOT_FOLDER_ID))
        #{
        #	return self::goToLogin();
        #}

        // we do not know if ref_id of request is accesible, so redirecting to root
        $_GET["ref_id"] = ROOT_FOLDER_ID;
        $_GET["cmd"] = "frameset";
        self::redirect(
            "ilias.php?baseClass=ilrepositorygui&reloadpublic=1&cmd=" .
            $_GET["cmd"] . "&ref_id=" . $_GET["ref_id"]
        );
    }

    /**
     * go to login
     *
     * @param int $a_auth_stat
     */
    protected static function goToLogin()
    {
        ilLoggerFactory::getLogger('init')->debug('Redirecting to login page.');

        if ($GLOBALS['DIC']['ilAuthSession']->isExpired()) {
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_EXPIRE);
        }
        if (!$GLOBALS['DIC']['ilAuthSession']->isAuthenticated()) {
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_LOGIN);
        }

        $script = "login.php?target=" . $_GET["target"] . "&client_id=" . $_COOKIE["ilClientId"] .
            "&auth_stat=" . $a_auth_stat;

        self::redirect(
            $script,
            "init_error_authentication_fail",
            array(
                "en" => "Authentication failed.",
                "de" => "Authentifizierung fehlgeschlagen.")
        );
    }

    /**
     * $lng initialisation
     */
    protected static function initLanguage($a_use_user_language = true)
    {
        global $DIC;

        /**
         * @var $rbacsystem ilRbacSystem
         */
        global $rbacsystem;

        require_once 'Services/Language/classes/class.ilLanguage.php';

        if ($a_use_user_language) {
            if ($DIC->offsetExists('lng')) {
                $DIC->offsetUnset('lng');
            }
            self::initGlobal('lng', ilLanguage::getGlobalInstance());
        } else {
            self::initGlobal('lng', ilLanguage::getFallbackInstance());
        }
        if (is_object($rbacsystem) && $DIC->offsetExists('tree')) {
            $rbacsystem->initMemberView();
        }
    }

    /**
     * $ilAccess and $rbac... initialisation
     */
    protected static function initAccessHandling()
    {
        self::initGlobal(
            "rbacreview",
            "ilRbacReview",
            "./Services/AccessControl/classes/class.ilRbacReview.php"
        );

        require_once "./Services/AccessControl/classes/class.ilRbacSystem.php";
        $rbacsystem = ilRbacSystem::getInstance();
        self::initGlobal("rbacsystem", $rbacsystem);

        self::initGlobal(
            "rbacadmin",
            "ilRbacAdmin",
            "./Services/AccessControl/classes/class.ilRbacAdmin.php"
        );

        self::initGlobal(
            "ilAccess",
            "ilAccess",
            "./Services/AccessControl/classes/class.ilAccess.php"
        );

        require_once "./Services/Conditions/classes/class.ilConditionHandler.php";
    }

    /**
     * Init log instance
     */
    protected static function initLog()
    {
        include_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
        $log = ilLoggerFactory::getRootLogger();

        self::initGlobal("ilLog", $log);
        // deprecated
        self::initGlobal("log", $log);
    }

    /**
     * Initialize global instance
     *
     * @param string $a_name
     * @param string $a_class
     * @param string $a_source_file
     */
    protected static function initGlobal($a_name, $a_class, $a_source_file = null)
    {
        global $DIC;

        if ($a_source_file) {
            include_once $a_source_file;
            $GLOBALS[$a_name] = new $a_class;
        } else {
            $GLOBALS[$a_name] = $a_class;
        }

        $DIC[$a_name] = function ($c) use ($a_name) {
            return $GLOBALS[$a_name];
        };
    }

    /**
     * Exit
     *
     * @param string $a_message
     */
    protected static function abortAndDie($a_message)
    {
        if (is_object($GLOBALS['ilLog'])) {
            $GLOBALS['ilLog']->write("Fatal Error: ilInitialisation - " . $a_message);
            $GLOBALS['ilLog']->logStack();
        }
        die($a_message);
    }

    /**
     * Prepare developer tools
     */
    protected static function handleDevMode()
    {
        if (defined(SHOWNOTICES) && SHOWNOTICES) {
            // no further differentiating of php version regarding to 5.4 neccessary
            // when the error reporting is set to E_ALL anyway

            // add notices to error reporting
            error_reporting(E_ALL);
        }

        if (defined('DEBUGTOOLS') && DEBUGTOOLS) {
            include_once "include/inc.debug.php";
        }
    }

    protected static $already_initialized;


    public static function reinitILIAS()
    {
        self::$already_initialized = false;
        self::initILIAS();
    }

    /**
     * ilias initialisation
     */
    public static function initILIAS()
    {
        if (self::$already_initialized) {
            return;
        }

        $GLOBALS["DIC"] = new \ILIAS\DI\Container();
        $GLOBALS["DIC"]["ilLoggerFactory"] = function ($c) {
            return ilLoggerFactory::getInstance();
        };

        self::$already_initialized = true;

        self::initCore();
        self::initHTTPServices($GLOBALS["DIC"]);
        if (ilContext::initClient()) {
            self::initClient();
            self::initFileUploadService($GLOBALS["DIC"]);
            self::initSession();

            if (ilContext::hasUser()) {
                self::initUser();

                if (ilContext::supportsPersistentSessions()) {
                    self::resumeUserSession();
                }
            }

            // init after Auth otherwise breaks CAS
            self::includePhp5Compliance();

            // language may depend on user setting
            self::initLanguage(true);
            $GLOBALS['DIC']['tree']->initLangCode();

            self::initInjector($GLOBALS['DIC']);
            self::initBackgroundTasks($GLOBALS['DIC']);
            self::initKioskMode($GLOBALS['DIC']);

            if (ilContext::hasHTML()) {
                self::initHTML();
            }
            self::initRefinery($GLOBALS['DIC']);
        }
    }

    /**
     * Init auth session.
     */
    protected static function initSession()
    {
        $GLOBALS["DIC"]["ilAuthSession"] = function ($c) {
            $auth_session = ilAuthSession::getInstance(
                $c['ilLoggerFactory']->getLogger('auth')
            );
            $auth_session->init();
            return $auth_session;
        };
    }


    /**
     * Set error reporting level
     */
    public static function handleErrorReporting()
    {
        // push the error level as high as possible / sane
        error_reporting(E_ALL & ~E_NOTICE);

        // see handleDevMode() - error reporting might be overwritten again
        // but we need the client ini first
    }

    /**
     * Init core objects (level 0)
     */
    protected static function initCore()
    {
        global $ilErr;

        self::handleErrorReporting();

        // breaks CAS: must be included after CAS context isset in AuthUtils
        //self::includePhp5Compliance();

        self::requireCommonIncludes();


        // error handler
        self::initGlobal(
            "ilErr",
            "ilErrorHandling",
            "./Services/Init/classes/class.ilErrorHandling.php"
        );
        $ilErr->setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, 'errorHandler'));

        // :TODO: obsolete?
        // PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, "errorHandler"));

        // workaround: load old post variables if error handler 'message' was called
        include_once "Services/Authentication/classes/class.ilSession.php";
        if (ilSession::get("message")) {
            $_POST = ilSession::get("post_vars");
        }

        self::removeUnsafeCharacters();

        self::initIliasIniFile();

        define('IL_INITIAL_WD', getcwd());

        // deprecated
        self::initGlobal("ilias", "ILIAS", "./Services/Init/classes/class.ilias.php");
    }

    /**
     * Init client-based objects (level 1)
     */
    protected static function initClient()
    {
        global $https, $ilias, $DIC;

        self::setCookieConstants();

        self::determineClient();

        self::bootstrapFilesystems();

        self::initClientIniFile();


        // --- needs client ini

        $ilias->client_id = CLIENT_ID;

        if (DEVMODE) {
            self::handleDevMode();
        }


        self::handleMaintenanceMode();

        self::initDatabase();

        // init dafault language
        self::initLanguage(false);

        // moved after databases
        self::initLog();

        self::initGlobal(
            "ilAppEventHandler",
            "ilAppEventHandler",
            "./Services/EventHandling/classes/class.ilAppEventHandler.php"
        );

        // there are rare cases where initILIAS is called twice for a request
        // example goto.php is called and includes ilias.php later
        // we must prevent that ilPluginAdmin is initialized twice in
        // this case, since this won't get the values out of plugin.php the
        // second time properly
        if (!isset($DIC["ilPluginAdmin"]) || !$DIC["ilPluginAdmin"] instanceof ilPluginAdmin) {
            self::initGlobal(
                "ilPluginAdmin",
                "ilPluginAdmin",
                "./Services/Component/classes/class.ilPluginAdmin.php"
            );
        }

        self::initSettings();
        self::setSessionHandler();
        self::initMail($GLOBALS['DIC']);
        self::initAvatar($GLOBALS['DIC']);
        self::initCustomObjectIcons($GLOBALS['DIC']);
        self::initTermsOfService($GLOBALS['DIC']);
        self::initAccessibilityControlConcept($GLOBALS['DIC']);


        // --- needs settings

        self::initLocale();

        if (ilContext::usesHTTP()) {
            // $https
            self::initGlobal("https", "ilHTTPS", "./Services/Http/classes/class.ilHTTPS.php");
            $https->enableSecureCookies();
            $https->checkPort();
        }


        // --- object handling

        self::initGlobal(
            "ilObjDataCache",
            "ilObjectDataCache",
            "./Services/Object/classes/class.ilObjectDataCache.php"
        );

        // needed in ilObjectDefinition
        require_once "./Services/Xml/classes/class.ilSaxParser.php";

        self::initGlobal(
            "objDefinition",
            "ilObjectDefinition",
            "./Services/Object/classes/class.ilObjectDefinition.php"
        );

        // $tree
        require_once "./Services/Tree/classes/class.ilTree.php";
        $tree = new ilTree(ROOT_FOLDER_ID);
        self::initGlobal("tree", $tree);
        unset($tree);

        self::initGlobal(
            "ilCtrl",
            "ilCtrl",
            "./Services/UICore/classes/class.ilCtrl.php"
        );

        self::setSessionCookieParams();

        // Init GlobalScreen
        self::initGlobalScreen($DIC);
    }

    /**
     * Init user / authentification (level 2)
     */
    protected static function initUser()
    {
        global $ilias, $ilUser;

        // $ilUser
        self::initGlobal(
            "ilUser",
            "ilObjUser",
            "./Services/User/classes/class.ilObjUser.php"
        );
        $ilias->account = $ilUser;

        self::initAccessHandling();
    }

    /**
     * Resume an existing user session
     */
    public static function resumeUserSession()
    {
        global $DIC;
        if (ilAuthUtils::isAuthenticationForced()) {
            ilAuthUtils::handleForcedAuthentication();
        }

        if (
            !$GLOBALS['DIC']['ilAuthSession']->isAuthenticated() or
            $GLOBALS['DIC']['ilAuthSession']->isExpired()
        ) {
            ilLoggerFactory::getLogger('init')->debug('Current session is invalid: ' . $GLOBALS['DIC']['ilAuthSession']->getId());
            $current_script = substr(strrchr($_SERVER["PHP_SELF"], "/"), 1);
            if (self::blockedAuthentication($current_script)) {
                ilLoggerFactory::getLogger('init')->debug('Authentication is started in current script.');
                // nothing todo: authentication is done in current script
                return;
            }

            return self::handleAuthenticationFail();
        }
        // valid session

        return self::initUserAccount();
    }

    /**
     * @static
     */
    protected static function handleAuthenticationSuccess()
    {
        /**
         * @var $ilUser ilObjUser
         */
        global $ilUser;

        require_once 'Services/Tracking/classes/class.ilOnlineTracking.php';
        ilOnlineTracking::updateAccess($ilUser);
    }

    /**
     * @static
     */
    protected static function handleAuthenticationFail()
    {
        /**
         * @var ilAuth
         * @var $ilSetting ilSetting
         */
        global $ilAuth, $ilSetting;

        ilLoggerFactory::getLogger('init')->debug('Handling of failed authentication.');

        // #10608
        if (
            ilContext::getType() == ilContext::CONTEXT_SOAP ||
            ilContext::getType() == ilContext::CONTEXT_WAC) {
            throw new Exception("Authentication failed.");
        }
        if (
            $GLOBALS['DIC']['ilAuthSession']->isExpired() &&
            !\ilObjUser::_isAnonymous($GLOBALS['DIC']['ilAuthSession']->getUserId())
        ) {
            ilLoggerFactory::getLogger('init')->debug('Expired session found -> redirect to login page');
            return self::goToLogin();
        }
        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME'])) {
            ilLoggerFactory::getLogger('init')->debug('Redirect to public section.');
            return self::goToPublicSection();
        }
        ilLoggerFactory::getLogger('init')->debug('Redirect to login page.');
        return self::goToLogin();
    }


    /**
     * @param \ILIAS\DI\Container $container
     */
    protected static function initHTTPServices(\ILIAS\DI\Container $container)
    {
        $container['http.request_factory'] = function ($c) {
            return new \ILIAS\HTTP\Request\RequestFactoryImpl();
        };

        $container['http.response_factory'] = function ($c) {
            return new \ILIAS\HTTP\Response\ResponseFactoryImpl();
        };

        $container['http.cookie_jar_factory'] = function ($c) {
            return new \ILIAS\HTTP\Cookies\CookieJarFactoryImpl();
        };

        $container['http.response_sender_strategy'] = function ($c) {
            return new \ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy();
        };

        $container['http'] = function ($c) {
            return new \ILIAS\DI\HTTPServices(
                $c['http.response_sender_strategy'],
                $c['http.cookie_jar_factory'],
                $c['http.request_factory'],
                $c['http.response_factory']
            );
        };
    }


    /**
     * @param \ILIAS\DI\Container $c
     */
    private static function initGlobalScreen(\ILIAS\DI\Container $c)
    {
        $c['global_screen'] = function () use ($c) {
            return new Services(new ilGSProviderFactory($c));
        };
        $c->globalScreen()->tool()->context()->stack()->clear();
        $c->globalScreen()->tool()->context()->claim()->main();
//        $c->globalScreen()->tool()->context()->current()->addAdditionalData('DEVMODE', (bool) DEVMODE);
    }

    /**
     * init the ILIAS UI framework.
     */
    public static function initUIFramework(\ILIAS\DI\Container $c)
    {
        $c["ui.factory"] = function ($c) {
            $c["lng"]->loadLanguageModule("ui");
            return new ILIAS\UI\Implementation\Factory(
                $c["ui.factory.counter"],
                $c["ui.factory.button"],
                $c["ui.factory.listing"],
                $c["ui.factory.image"],
                $c["ui.factory.panel"],
                $c["ui.factory.modal"],
                $c["ui.factory.dropzone"],
                $c["ui.factory.popover"],
                $c["ui.factory.divider"],
                $c["ui.factory.link"],
                $c["ui.factory.dropdown"],
                $c["ui.factory.item"],
                $c["ui.factory.viewcontrol"],
                $c["ui.factory.chart"],
                $c["ui.factory.input"],
                $c["ui.factory.table"],
                $c["ui.factory.messagebox"],
                $c["ui.factory.card"],
                $c["ui.factory.layout"],
                $c["ui.factory.maincontrols"],
                $c["ui.factory.tree"],
                $c["ui.factory.menu"],
                $c["ui.factory.symbol"],
                $c["ui.factory.legacy"]
            );
        };
        $c["ui.signal_generator"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\SignalGenerator;
        };
        $c["ui.factory.counter"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Counter\Factory();
        };
        $c["ui.factory.button"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Button\Factory();
        };
        $c["ui.factory.listing"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Listing\Factory();
        };
        $c["ui.factory.image"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Image\Factory();
        };
        $c["ui.factory.panel"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Panel\Factory($c["ui.factory.panel.listing"]);
        };
        $c["ui.factory.modal"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Modal\Factory($c["ui.signal_generator"]);
        };
        $c["ui.factory.dropzone"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Dropzone\Factory($c["ui.factory.dropzone.file"]);
        };
        $c["ui.factory.popover"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Popover\Factory($c["ui.signal_generator"]);
        };
        $c["ui.factory.divider"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Divider\Factory();
        };
        $c["ui.factory.link"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Link\Factory();
        };
        $c["ui.factory.dropdown"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Dropdown\Factory();
        };
        $c["ui.factory.item"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Item\Factory();
        };
        $c["ui.factory.viewcontrol"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\ViewControl\Factory($c["ui.signal_generator"]);
        };
        $c["ui.factory.chart"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Chart\Factory($c["ui.factory.progressmeter"]);
        };
        $c["ui.factory.input"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Factory(
                $c["ui.signal_generator"],
                $c["ui.factory.input.field"],
                $c["ui.factory.input.container"]
            );
        };
        $c["ui.factory.table"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Table\Factory($c["ui.signal_generator"]);
        };
        $c["ui.factory.messagebox"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\MessageBox\Factory();
        };
        $c["ui.factory.card"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Card\Factory();
        };
        $c["ui.factory.layout"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Layout\Factory();
        };
        $c["ui.factory.maincontrols.slate"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\MainControls\Slate\Factory(
                $c['ui.signal_generator'],
                $c['ui.factory.counter'],
                $c["ui.factory.symbol"]
            );
        };
        $c["ui.factory.maincontrols"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\MainControls\Factory(
                $c['ui.signal_generator'],
                $c['ui.factory.maincontrols.slate']
            );
        };
        $c["ui.factory.menu"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Menu\Factory();
        };
        $c["ui.factory.symbol.glyph"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory();
        };
        $c["ui.factory.symbol.icon"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Symbol\Icon\Factory();
        };
        $c["ui.factory.symbol.avatar"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Symbol\Avatar\Factory();
        };
        $c["ui.factory.symbol"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Symbol\Factory(
                $c["ui.factory.symbol.icon"],
                $c["ui.factory.symbol.glyph"],
                $c["ui.factory.symbol.avatar"]
            );
        };
        $c["ui.factory.progressmeter"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Chart\ProgressMeter\Factory();
        };
        $c["ui.factory.dropzone.file"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Dropzone\File\Factory();
        };
        $c["ui.factory.input.field"] = function ($c) {
            $data_factory = new ILIAS\Data\Factory();
            $refinery = new ILIAS\Refinery\Factory($data_factory, $c["lng"]);

            return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
                $c["ui.signal_generator"],
                $data_factory,
                $refinery,
                $c["lng"]
            );
        };
        $c["ui.factory.input.container"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Container\Factory(
                $c["ui.factory.input.container.form"],
                $c["ui.factory.input.container.filter"]
            );
        };
        $c["ui.factory.input.container.form"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Container\Form\Factory(
                $c["ui.factory.input.field"]
            );
        };
        $c["ui.factory.input.container.filter"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory(
                $c["ui.signal_generator"],
                $c["ui.factory.input.field"]
            );
        };
        $c["ui.factory.panel.listing"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Panel\Listing\Factory();
        };

        $c["ui.renderer"] = function ($c) {
            return new ILIAS\UI\Implementation\DefaultRenderer(
                $c["ui.component_renderer_loader"]
                );
        };
        $c["ui.component_renderer_loader"] = function ($c) {
            return new ILIAS\UI\Implementation\Render\LoaderCachingWrapper(
                new ILIAS\UI\Implementation\Render\LoaderResourceRegistryWrapper(
                    $c["ui.resource_registry"],
                    new ILIAS\UI\Implementation\Render\FSLoader(
                    new ILIAS\UI\Implementation\Render\DefaultRendererFactory(
                            $c["ui.factory"],
                            $c["ui.template_factory"],
                            $c["lng"],
                            $c["ui.javascript_binding"],
                            $c["refinery"]
                            ),
                    new ILIAS\UI\Implementation\Component\Symbol\Glyph\GlyphRendererFactory(
                            $c["ui.factory"],
                            $c["ui.template_factory"],
                            $c["lng"],
                            $c["ui.javascript_binding"],
                            $c["refinery"]
                          ),
                    new ILIAS\UI\Implementation\Component\Input\Field\FieldRendererFactory(
                            $c["ui.factory"],
                            $c["ui.template_factory"],
                            $c["lng"],
                            $c["ui.javascript_binding"],
                            $c["refinery"]
                          )
                        )
                    )
                );
        };
        $c["ui.template_factory"] = function ($c) {
            return new ILIAS\UI\Implementation\Render\ilTemplateWrapperFactory(
                $c["tpl"]
                );
        };
        $c["ui.resource_registry"] = function ($c) {
            return new ILIAS\UI\Implementation\Render\ilResourceRegistry($c["tpl"]);
        };
        $c["ui.javascript_binding"] = function ($c) {
            return new ILIAS\UI\Implementation\Render\ilJavaScriptBinding($c["tpl"]);
        };

        $c["ui.factory.tree"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Tree\Factory($c["ui.signal_generator"]);
        };

        $c["ui.factory.legacy"] = function ($c) {
            return new ILIAS\UI\Implementation\Component\Legacy\Factory($c["ui.signal_generator"]);
        };

        $plugins = ilPluginAdmin::getActivePlugins();
        foreach ($plugins as $plugin_data) {
            $plugin = ilPluginAdmin::getPluginObject($plugin_data["component_type"], $plugin_data["component_name"], $plugin_data["slot_id"], $plugin_data["name"]);

            $c['ui.renderer'] = $plugin->exchangeUIRendererAfterInitialization($c);

            foreach ($c->keys() as $key) {
                if (strpos($key, "ui.factory") === 0) {
                    $c[$key] = $plugin->exchangeUIFactoryAfterInitialization($key, $c);
                }
            }
        }
    }

    /**
     * @param \ILIAS\DI\Container $container
     */
    protected static function initRefinery(\ILIAS\DI\Container $container)
    {
        $container['refinery'] = function ($container) {
            $dataFactory = new \ILIAS\Data\Factory();
            $language = $container['lng'];

            return new \ILIAS\Refinery\Factory($dataFactory, $language);
        };
    }

    /**
     * init HTML output (level 3)
     */
    protected static function initHTML()
    {
        global $ilUser, $DIC;

        if (ilContext::hasUser()) {
            // load style definitions
            // use the init function with plugin hook here, too
            self::initStyle();
        }

        self::initUIFramework($GLOBALS["DIC"]);
        $tpl = new ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());
        self::initGlobal("tpl", $tpl);

        if (ilContext::hasUser()) {
            $request_adjuster = new ilUserRequestTargetAdjustment(
                $ilUser,
                $GLOBALS['DIC']['ilCtrl'],
                $GLOBALS['DIC']->http()->request()
            );
            $request_adjuster->adjust();
        }

        require_once "./Services/UICore/classes/class.ilFrameTargetInfo.php";

        self::initGlobal(
            "ilNavigationHistory",
            "ilNavigationHistory",
            "Services/Navigation/classes/class.ilNavigationHistory.php"
        );

        self::initGlobal(
            "ilBrowser",
            "ilBrowser",
            "./Services/Utilities/classes/class.ilBrowser.php"
        );

        self::initGlobal(
            "ilHelp",
            "ilHelpGUI",
            "Services/Help/classes/class.ilHelpGUI.php"
        );

        self::initGlobal(
            "ilToolbar",
            "ilToolbarGUI",
            "./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php"
        );

        self::initGlobal(
            "ilLocator",
            "ilLocatorGUI",
            "./Services/Locator/classes/class.ilLocatorGUI.php"
        );

        self::initGlobal(
            "ilTabs",
            "ilTabsGUI",
            "./Services/UIComponent/Tabs/classes/class.ilTabsGUI.php"
        );

        if (ilContext::hasUser()) {
            include_once './Services/MainMenu/classes/class.ilMainMenuGUI.php';
            $ilMainMenu = new ilMainMenuGUI("_top");

            self::initGlobal("ilMainMenu", $ilMainMenu);
            unset($ilMainMenu);

            // :TODO: tableGUI related

            // set hits per page for all lists using table module
            $_GET['limit'] = (int) $ilUser->getPref('hits_per_page');
            ilSession::set('tbl_limit', $_GET['limit']);

            // the next line makes it impossible to save the offset somehow in a session for
            // a specific table (I tried it for the user administration).
            // its not posssible to distinguish whether it has been set to page 1 (=offset = 0)
            // or not set at all (then we want the last offset, e.g. being used from a session var).
            // So I added the wrapping if statement. Seems to work (hopefully).
            // Alex April 14th 2006
            if (isset($_GET['offset']) && $_GET['offset'] != "") {							// added April 14th 2006
                $_GET['offset'] = (int) $_GET['offset'];		// old code
            }

            self::initGlobal("lti", "ilLTIViewGUI", "./Services/LTI/classes/class.ilLTIViewGUI.php");
            $GLOBALS["DIC"]["lti"]->init();
            self::initKioskMode($GLOBALS["DIC"]);
        } else {
            // several code parts rely on ilObjUser being always included
            include_once "Services/User/classes/class.ilObjUser.php";
        }
    }

    /**
     * Extract current cmd from request
     *
     * @return string
     */
    protected static function getCurrentCmd()
    {
        $cmd = $_REQUEST["cmd"];
        if (is_array($cmd)) {
            return array_shift(array_keys($cmd));
        } else {
            return $cmd;
        }
    }

    /**
     * Block authentication based on current request
     *
     * @return boolean
     */
    protected static function blockedAuthentication($a_current_script)
    {
        if (ilContext::getType() == ilContext::CONTEXT_WAC) {
            ilLoggerFactory::getLogger('init')->debug('Blocked authentication for WAC request.');
            return true;
        }
        if (ilContext::getType() == ilContext::CONTEXT_APACHE_SSO) {
            ilLoggerFactory::getLogger('init')->debug('Blocked authentication for sso request.');
            return true;
        }
        if (ilContext::getType() == ilContext::CONTEXT_WEBDAV) {
            ilLoggerFactory::getLogger('init')->debug('Blocked authentication for webdav request');
            return true;
        }
        if (ilContext::getType() == ilContext::CONTEXT_SHIBBOLETH) {
            ilLoggerFactory::getLogger('init')->debug('Blocked authentication for shibboleth request.');
            return true;
        }
        if (ilContext::getType() == ilContext::CONTEXT_LTI_PROVIDER) {
            ilLoggerFactory::getLogger('init')->debug('Blocked authentication for lti provider requests.');
            return true;
        }
        if (ilContext::getType() == ilContext::CONTEXT_SAML) {
            ilLoggerFactory::getLogger('init')->debug('Blocked authentication for SAML request.');
            return true;
        }
        if (
            $a_current_script == "register.php" ||
            $a_current_script == "pwassist.php" ||
            $a_current_script == "confirmReg.php" ||
            $a_current_script == "il_securimage_play.php" ||
            $a_current_script == "il_securimage_show.php" ||
            $a_current_script == 'login.php'
        ) {
            ilLoggerFactory::getLogger('auth')->debug('Blocked authentication for script: ' . $a_current_script);
            return true;
        }

        if ($_REQUEST["baseClass"] == "ilStartUpGUI") {
            $cmd_class = $_REQUEST["cmdClass"];

            if ($cmd_class == "ilaccountregistrationgui" ||
                $cmd_class == "ilpasswordassistancegui") {
                ilLoggerFactory::getLogger('auth')->debug('Blocked authentication for cmdClass: ' . $cmd_class);
                return true;
            }

            $cmd = self::getCurrentCmd();
            if (
                $cmd == "showTermsOfService" || $cmd == "showClientList" ||
                $cmd == 'showAccountMigration' || $cmd == 'migrateAccount' ||
                $cmd == 'processCode' || $cmd == 'showLoginPage' || $cmd == 'showLogout' ||
                $cmd == 'doStandardAuthentication' || $cmd == 'doCasAuthentication'
            ) {
                ilLoggerFactory::getLogger('auth')->debug('Blocked authentication for cmd: ' . $cmd);
                return true;
            }
        }

        // #12884
        if (($a_current_script == "goto.php" && $_GET["target"] == "impr_0") ||
            $_GET["baseClass"] == "ilImprintGUI") {
            ilLoggerFactory::getLogger('auth')->debug('Blocked authentication for baseClass: ' . $_GET['baseClass']);
            return true;
        }

        if ($a_current_script == 'goto.php' && in_array($_GET['target'], array(
            'usr_registration', 'usr_nameassist', 'usr_pwassist', 'usr_agreement'
        ))) {
            ilLoggerFactory::getLogger('auth')->debug('Blocked authentication for goto target: ' . $_GET['target']);
            return true;
        }

        ilLoggerFactory::getLogger('auth')->debug('Authentication required');
        return false;
    }

    /**
     * Translate message if possible
     *
     * @param string $a_message_id
     * @param array $a_message_static
     * @return string
     */
    protected static function translateMessage($a_message_id, array $a_message_static = null)
    {
        global $ilDB, $lng, $ilSetting, $ilClientIniFile, $ilUser;

        // current language
        if (!$lng) {
            $lang = "en";
            if ($ilUser) {
                $lang = $ilUser->getLanguage();
            } elseif ($_REQUEST["lang"]) {
                $lang = (string) $_REQUEST["lang"];
            } elseif ($ilSetting) {
                $lang = $ilSetting->get("language");
            } elseif ($ilClientIniFile) {
                $lang = $ilClientIniFile->readVariable("language", "default");
            }
        } else {
            $lang = $lng->getLangKey();
        }

        $message = "";
        if ($ilDB && $a_message_id) {
            if (!$lng) {
                require_once "./Services/Language/classes/class.ilLanguage.php";
                $lng = new ilLanguage($lang);
            }

            $lng->loadLanguageModule("init");
            $message = $lng->txt($a_message_id);
        } elseif (is_array($a_message_static)) {
            if (!isset($a_message_static[$lang])) {
                $lang = "en";
            }
            $message = $a_message_static[$lang];
        }
        return $message;
    }

    /**
     * Redirects to target url if context supports it
     *
     * @param string $a_target
     * @param string $a_message_id
     * @param array $a_message_details
     */
    protected static function redirect($a_target, $a_message_id = '', array $a_message_static = null)
    {
        // #12739
        if (defined("ILIAS_HTTP_PATH") &&
            !stristr($a_target, ILIAS_HTTP_PATH)) {
            $a_target = ILIAS_HTTP_PATH . "/" . $a_target;
        }

        foreach (['ext_uid', 'soap_pw'] as $param) {
            if (false === strpos($a_target, $param . '=') && isset($GLOBALS['DIC']->http()->request()->getQueryParams()[$param])) {
                $a_target = \ilUtil::appendUrlParameterString($a_target, $param . '=' . \ilUtil::stripSlashes(
                    $GLOBALS['DIC']->http()->request()->getQueryParams()[$param]
                ));
            }
        }

        if (ilContext::supportsRedirects()) {
            ilUtil::redirect($a_target);
        } else {
            $message = self::translateMessage($a_message_id, $a_message_static);

            // user-directed linked message
            if (ilContext::usesHTTP() && ilContext::hasHTML()) {
                $link = self::translateMessage(
                    "init_error_redirect_click",
                    array("en" => 'Please click to continue.',
                        "de" => 'Bitte klicken um fortzufahren.')
                );
                $mess = $message .
                    '<br /><a href="' . $a_target . '">' . $link . '</a>';
            }
            // plain text
            else {
                // not much we can do here
                $mess = $message;

                if (!trim($mess)) {
                    $mess = self::translateMessage(
                        "init_error_redirect_info",
                        array("en" => 'Redirect not supported by context.',
                            "de" => 'Weiterleitungen werden durch Kontext nicht unterstützt.')
                    ) .
                    ' (' . $a_target . ')';
                }
            }

            self::abortAndDie($mess);
        }
    }

    /**
     * Requires valid authenticated user
     */
    public static function redirectToStartingPage()
    {
        /**
         * @var $ilUser ilObjUser
         */
        global $ilUser;

        // fallback, should never happen
        if ($ilUser->getId() == ANONYMOUS_USER_ID) {
            ilInitialisation::goToPublicSection();
            return true;
        }

        // for password change and incomplete profile
        // see ilDashboardGUI
        if (!$_GET["target"]) {
            ilLoggerFactory::getLogger('init')->debug('Redirect to default starting page');
            // Redirect here to switch back to http if desired
            include_once './Services/User/classes/class.ilUserUtil.php';
            ilUtil::redirect(ilUserUtil::getStartingPointAsUrl());
        } else {
            ilLoggerFactory::getLogger('init')->debug('Redirect to target: ' . $_GET['target']);
            ilUtil::redirect("goto.php?target=" . $_GET["target"]);
        }
    }


    private static function initBackgroundTasks(\ILIAS\DI\Container $c)
    {
        global $ilIliasIniFile;

        $n_of_tasks = $ilIliasIniFile->readVariable("background_tasks", "number_of_concurrent_tasks");
        $sync = $ilIliasIniFile->readVariable("background_tasks", "concurrency");

        $n_of_tasks = $n_of_tasks ? $n_of_tasks : 5;
        $sync = $sync ? $sync : 'sync'; // The default value is sync.

        $c["bt.task_factory"] = function ($c) {
            return new \ILIAS\BackgroundTasks\Implementation\Tasks\BasicTaskFactory($c["di.injector"]);
        };

        $c["bt.persistence"] = function ($c) {
            return BasicPersistence::instance();
        };

        $c["bt.injector"] = function ($c) {
            return new \ILIAS\BackgroundTasks\Dependencies\Injector($c, new BaseDependencyMap());
        };

        $c["bt.task_manager"] = function ($c) use ($sync) {
            if ($sync == 'sync') {
                return new \ILIAS\BackgroundTasks\Implementation\TaskManager\SyncTaskManager($c["bt.persistence"]);
            } elseif ($sync == 'async') {
                return new \ILIAS\BackgroundTasks\Implementation\TaskManager\AsyncTaskManager($c["bt.persistence"]);
            } else {
                throw new ilException("The supported Background Task Managers are sync and async. $sync given.");
            }
        };
    }


    private static function initInjector(\ILIAS\DI\Container $c)
    {
        $c["di.dependency_map"] = function ($c) {
            return new \ILIAS\BackgroundTasks\Dependencies\DependencyMap\BaseDependencyMap();
        };

        $c["di.injector"] = function ($c) {
            return new \ILIAS\BackgroundTasks\Dependencies\Injector($c, $c["di.dependency_map"]);
        };
    }

    private static function initKioskMode(\ILIAS\DI\Container $c)
    {
        $c["service.kiosk_mode"] = function ($c) {
            return new ilKioskModeService(
                $c['ilCtrl'],
                $c['lng'],
                $c['ilAccess'],
                $c['objDefinition']
            );
        };
    }
}
