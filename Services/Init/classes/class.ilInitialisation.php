<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

// TODO:
use ILIAS\BackgroundTasks\Dependencies\DependencyMap\BaseDependencyMap;
use ILIAS\DI\Container;
use ILIAS\Filesystem\Provider\FilesystemFactory;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizerImpl;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\Location;
use ILIAS\FileUpload\Processor\BlacklistExtensionPreProcessor;
use ILIAS\FileUpload\Processor\FilenameSanitizerPreProcessor;
use ILIAS\FileUpload\Processor\PreProcessorManagerImpl;
use ILIAS\GlobalScreen\Services;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\HTTP\Wrapper\SuperGlobalDropInReplacement;
use ILIAS\ResourceStorage\Policy\WhiteAndBlacklistedFileNamePolicy;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\FileSystemStorageHandler;
use ILIAS\ResourceStorage\Resource\Repository\ResourceDBRepository;
use ILIAS\ResourceStorage\Revision\Repository\RevisionDBRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationDBRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderDBRepository;
use ILIAS\ResourceStorage\Preloader\DBRepositoryPreloader;
use ILIAS\Filesystem\Definitions\SuffixDefinitions;

require_once("libs/composer/vendor/autoload.php");

// needed for slow queries, etc.
if (!isset($GLOBALS['ilGlobalStartTime']) || !$GLOBALS['ilGlobalStartTime']) {
    $GLOBALS['ilGlobalStartTime'] = microtime();
}

global $DIC;
if (null === $DIC) {
    // Don't remove this, intellisense autocompletion does not work in PhpStorm without a top level assignment
    $DIC = new Container();
}

/** @defgroup ServicesInit Services/Init
 */

/**
 * ILIAS Initialisation Utility Class
 * perform basic setup: init database handler, load configuration file,
 * init user authentification & error handler, load object type definitions
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Sascha Hofmann <shofmann@databay.de>
 * @version $Id$
 * @ingroup ServicesInit
 */
class ilInitialisation
{
    /**
     * Remove unsafe characters from GET
     */
    protected static function removeUnsafeCharacters(): void
    {
        // Remove unsafe characters from GET parameters.
        // We do not need this characters in any case, so it is
        // feasible to filter them everytime. POST parameters
        // need attention through ilUtil::stripSlashes() and similar functions)
        $_GET = self::recursivelyRemoveUnsafeCharacters($_GET);
    }

    /**
     * @param array|string $var
     * @return array|string
     */
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
    protected static function requireCommonIncludes(): void
    {
        /** @noRector */
        require_once "include/inc.ilias_version.php";

        self::initGlobal("ilBench", "ilBenchmark", "./Services/Utilities/classes/class.ilBenchmark.php");
    }

    /**
     * This is a hack for  authentication.
     * Since the phpCAS lib ships with its own compliance functions.
     */
    protected static function includePhp5Compliance(): void
    {
        if (ilAuthFactory::getContext() != ilAuthFactory::CONTEXT_CAS) {
            /** @noRector */
            require_once("include/inc.xml5compliance.php");
        }
        /** @noRector */
        require_once("include/inc.xsl5compliance.php");
    }

    /**
     * This method provides a global instance of class ilIniFile for the
     * ilias.ini.php file in variable $ilIliasIniFile.
     * It initializes a lot of constants accordingly to the settings in
     * the ilias.ini.php file.
     */
    protected static function initIliasIniFile(): void
    {
        $ilIliasIniFile = new ilIniFile("./ilias.ini.php");
        $ilIliasIniFile->read();
        self::initGlobal('ilIliasIniFile', $ilIliasIniFile);

        // initialize constants
        define("ILIAS_DATA_DIR", $ilIliasIniFile->readVariable("clients", "datadir"));
        define("ILIAS_WEB_DIR", $ilIliasIniFile->readVariable("clients", "path"));
        if (!defined("ILIAS_ABSOLUTE_PATH")) {
            define("ILIAS_ABSOLUTE_PATH", $ilIliasIniFile->readVariable('server', 'absolute_path'));
        }

        // logging
        define("ILIAS_LOG_DIR", $ilIliasIniFile->readVariable("log", "path"));
        define("ILIAS_LOG_FILE", $ilIliasIniFile->readVariable("log", "file"));
        if (!defined("ILIAS_LOG_ENABLED")) {
            define("ILIAS_LOG_ENABLED", $ilIliasIniFile->readVariable("log", "enabled"));
        }
        define("ILIAS_LOG_LEVEL", $ilIliasIniFile->readVariable("log", "level"));

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
                define(
                    "ERROR_EDITOR_PATH_TRANSLATIONS",
                    $ilIliasIniFile->readVariable('error', 'editor_path_translations')
                );
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
            case "icap":
                define("IL_VIRUS_SCANNER", "icap");
                define("IL_ICAP_HOST", $ilIliasIniFile->readVariable("tools", "i_cap_host"));
                define("IL_ICAP_PORT", $ilIliasIniFile->readVariable("tools", "i_cap_port"));
                define("IL_ICAP_AV_COMMAND", $ilIliasIniFile->readVariable("tools", "i_cap_av_command"));
                define("IL_ICAP_CLIENT", $ilIliasIniFile->readVariable("tools", "i_cap_client"));
                define("IL_VIRUS_CLEAN_COMMAND", '');
                break;

            default:
                define("IL_VIRUS_SCANNER", "None");
                define("IL_VIRUS_CLEAN_COMMAND", '');
                break;
        }

        $tz = ilTimeZone::initDefaultTimeZone($ilIliasIniFile);
        define("IL_TIMEZONE", $tz);
    }

    protected static function initResourceStorage(): void
    {
        global $DIC;

        $DIC['resource_storage'] = static function (Container $c): \ILIAS\ResourceStorage\Services {
            $revision_repository = new RevisionDBRepository($c->database());
            $resource_repository = new ResourceDBRepository($c->database());
            $information_repository = new InformationDBRepository($c->database());
            $stakeholder_repository = new StakeholderDBRepository($c->database());
            return new \ILIAS\ResourceStorage\Services(
                new StorageHandlerFactory([
                    new MaxNestingFileSystemStorageHandler($c['filesystem.storage'], Location::STORAGE),
                    new FileSystemStorageHandler($c['filesystem.storage'], Location::STORAGE)
                ]),
                $revision_repository,
                $resource_repository,
                $information_repository,
                $stakeholder_repository,
                new LockHandlerilDB($c->database()),
                new ilFileServicesPolicy($c->fileServiceSettings()),
                new DBRepositoryPreloader(
                    $c->database(),
                    $resource_repository,
                    $revision_repository,
                    $information_repository,
                    $stakeholder_repository
                )
            );
        };
    }

    /**
     * Bootstraps the ILIAS filesystem abstraction.
     * The bootstrapped abstraction are:
     *  - temp
     *  - web
     *  - storage
     *  - customizing
     * @return void
     * @since 5.3
     */
    public static function bootstrapFilesystems(): void
    {
        global $DIC;

        $DIC['filesystem.security.sanitizing.filename'] = function (Container $c) {
            return new ilFileServicesFilenameSanitizer(
                $c->fileServiceSettings()
            );
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

        $DIC['filesystem.node_modules'] = function ($c) {
            //customizing

            /**
             * @var FilesystemFactory $delegatingFactory
             */
            $delegatingFactory = $c['filesystem.factory'];
            $customizingConfiguration = new \ILIAS\Filesystem\Provider\Configuration\LocalConfig(ILIAS_ABSOLUTE_PATH . '/' . 'node_modules');
            return $delegatingFactory->getLocal($customizingConfiguration, true);
        };

        $DIC['filesystem'] = function ($c) {
            return new \ILIAS\Filesystem\FilesystemsImpl(
                $c['filesystem.storage'],
                $c['filesystem.web'],
                $c['filesystem.temp'],
                $c['filesystem.customizing'],
                $c['filesystem.libs'],
                $c['filesystem.node_modules']
            );
        };
    }

    /**
     * Initializes the file upload service.
     * This service requires the http and filesystem service.
     * @param \ILIAS\DI\Container $dic The dependency container which should be used to load the file upload service.
     * @return void
     */
    public static function initFileUploadService(\ILIAS\DI\Container $dic): void
    {
        $dic['upload.processor-manager'] = function ($c) {
            return new PreProcessorManagerImpl();
        };

        $dic['upload'] = function (\ILIAS\DI\Container $c) {
            $fileUploadImpl = new \ILIAS\FileUpload\FileUploadImpl(
                $c['upload.processor-manager'],
                $c['filesystem'],
                $c['http']
            );
            if ((defined('IL_VIRUS_SCANNER') && IL_VIRUS_SCANNER != "None") || (defined('IL_SCANNER_TYPE') && IL_SCANNER_TYPE == "1")) {
                $fileUploadImpl->register(new ilVirusScannerPreProcessor(ilVirusScannerFactory::_getInstance()));
            }

            $fileUploadImpl->register(new FilenameSanitizerPreProcessor());
            $fileUploadImpl->register(
                new ilFileServicesPreProcessor(
                    $c->rbac()->system(),
                    $c->fileServiceSettings(),
                    $c->language()->txt("msg_info_blacklisted")
                )
            );

            return $fileUploadImpl;
        };
    }

    /**
     * builds http path
     */
    protected static function buildHTTPPath(): bool
    {
        global $DIC;

        if ($DIC['https']->isDetected()) {
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
            if (isset($path['extension']) && $path['extension'] !== '') {
                $uri = dirname($rq_uri);
            } else {
                $uri = $rq_uri;
            }
        } else {
            // if in module remove module name from HTTP_PATH
            $path = dirname($rq_uri);

            // dirname cuts the last directory from a directory path e.g content/classes return content
            $module = ilFileUtils::removeTrailingPathSeparators(ILIAS_MODULE);

            $dirs = explode('/', $module);
            $uri = $path;
            foreach ($dirs as $dir) {
                $uri = dirname($uri);
            }
        }

        $iliasHttpPath = ilContext::modifyHttpPath(implode('', [$protocol, $host, $uri]));

        $f = new \ILIAS\Data\Factory();
        $uri = $f->uri(ilFileUtils::removeTrailingPathSeparators($iliasHttpPath));

        return define('ILIAS_HTTP_PATH', $uri->getBaseURI());
    }

    /**
     * This method determines the current client and sets the
     * constant CLIENT_ID.
     */
    protected static function determineClient(): void
    {
        global $DIC;
        $df = new \ILIAS\Data\Factory();

        // check whether ini file object exists
        if (!$DIC->isDependencyAvailable('iliasIni')) {
            self::abortAndDie('Fatal Error: ilInitialisation::determineClient called without initialisation of ILIAS ini file object.');
        }
        $in_unit_tests = defined('IL_PHPUNIT_TEST');
        $context_supports_persitent_session = ilContext::supportsPersistentSessions();
        $can_set_cookie = !$in_unit_tests && $context_supports_persitent_session;
        $has_request_client_id = $DIC->http()->wrapper()->query()->has('client_id');
        $has_cookie_client_id = $DIC->http()->cookieJar()->has('ilClientId');
        $default_client_id = $DIC->iliasIni()->readVariable('clients', 'default');

        // determintaion of client_id:
        $client_id_to_use = '';
        // first we try to get the client_id from request
        if ($has_request_client_id) {
            // @todo refinerey undefined
            $client_id_from_get = (string) $_GET['client_id'];
        }
        // we found a client_id in $GET
        if (isset($client_id_from_get) && strlen($client_id_from_get) > 0) {
            $client_id_to_use = $_GET['client_id'] = $df->clientId($client_id_from_get)->toString();
            if ($can_set_cookie) {
                ilUtil::setCookie('ilClientId', $client_id_to_use);
            }
        } else {
            $client_id_to_use = $default_client_id;
            if (!isset($_COOKIE['ilClientId'])) {
                ilUtil::setCookie('ilClientId', $client_id_to_use);
            }
        }
        $client_id_to_use = strlen($client_id_to_use) > 0 ? $client_id_to_use : $default_client_id;

        define('CLIENT_ID', $df->clientId($client_id_to_use)->toString());
    }

    /**
     * This method provides a global instance of class ilIniFile for the
     * client.ini.php file in variable $ilClientIniFile.
     * It initializes a lot of constants accordingly to the settings in
     * the client.ini.php file.
     * Preconditions: ILIAS_WEB_DIR and CLIENT_ID must be set.
     * @return    void        true, if no error occured with client init file
     *                        otherwise false
     */
    protected static function initClientIniFile(): void
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
                              "de" => "Mandant ist ungültig."
                );
                self::redirect("index.php?client_id=" . $default_client, '', $mess);
            } else {
                self::abortAndDie("Fatal Error: ilInitialisation::initClientIniFile initializing client ini file abborted with: " . $ilClientIniFile->ERROR);
            }
        }

        self::initGlobal("ilClientIniFile", $ilClientIniFile);
        // set constants
        define("DEVMODE", (int) $ilClientIniFile->readVariable("system", "DEVMODE"));
        define("SHOWNOTICES", (int) $ilClientIniFile->readVariable("system", "SHOWNOTICES"));
        if (!defined("ROOT_FOLDER_ID")) {
            define("ROOT_FOLDER_ID", (int) $ilClientIniFile->readVariable('system', 'ROOT_FOLDER_ID'));
        }
        if (!defined("SYSTEM_FOLDER_ID")) {
            define("SYSTEM_FOLDER_ID", (int) $ilClientIniFile->readVariable('system', 'SYSTEM_FOLDER_ID'));
        }
        if (!defined("ROLE_FOLDER_ID")) {
            define("ROLE_FOLDER_ID", (int) $ilClientIniFile->readVariable('system', 'ROLE_FOLDER_ID'));
        }
        define("MAIL_SETTINGS_ID", (int) $ilClientIniFile->readVariable('system', 'MAIL_SETTINGS_ID'));
        $error_handler = $ilClientIniFile->readVariable('system', 'ERROR_HANDLER');
        define("ERROR_HANDLER", $error_handler ?: "PRETTY_PAGE");

        // this is for the online help installation, which sets OH_REF_ID to the
        // ref id of the online module
        define("OH_REF_ID", (int) $ilClientIniFile->readVariable("system", "OH_REF_ID"));

        // see ilObject::TITLE_LENGTH, ilObject::DESC_LENGTH
        // define ("MAXLENGTH_OBJ_TITLE",125);#$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_TITLE'));
        // define ("MAXLENGTH_OBJ_DESC",$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_DESC'));

        if (!defined("CLIENT_DATA_DIR")) {
            define("CLIENT_DATA_DIR", ILIAS_DATA_DIR . "/" . CLIENT_ID);
        }
        if (!defined("CLIENT_WEB_DIR")) {
            define("CLIENT_WEB_DIR", ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . CLIENT_ID);
        }
        define("CLIENT_NAME", $ilClientIniFile->readVariable('client', 'name')); // Change SS

        $db_type = $ilClientIniFile->readVariable("db", "type");
        if ($db_type === "") {
            define("IL_DB_TYPE", ilDBConstants::TYPE_INNODB);
        } else {
            define("IL_DB_TYPE", $db_type);
        }

        $ilGlobalCacheSettings = new ilGlobalCacheSettings();
        $ilGlobalCacheSettings->readFromIniFile($ilClientIniFile);
        ilGlobalCache::setup($ilGlobalCacheSettings);
    }

    /**
     * handle maintenance mode
     */
    protected static function handleMaintenanceMode(): void
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
     */
    protected static function initDatabase(): void
    {
        // build dsn of database connection and connect
        $ilDB = ilDBWrapperFactory::getWrapper(IL_DB_TYPE);
        $ilDB->initFromIniFile();
        $ilDB->connect();

        self::initGlobal("ilDB", $ilDB);
    }

    /**
     * set session handler to db
     * Used in Soap/CAS
     */
    public static function setSessionHandler(): void
    {
        $db_session_handler = new ilSessionDBHandler();
        if (!$db_session_handler->setSaveHandler()) {
            self::abortAndDie("Cannot start session handling.");
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
    protected static function setCookieConstants(): void
    {
        if (ilAuthFactory::getContext() == ilAuthFactory::CONTEXT_HTTP) {
            $cookie_path = '/';
        } elseif (isset($GLOBALS['COOKIE_PATH'])) {
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
    protected static function setSessionCookieParams(): void
    {
        global $ilSetting, $DIC;

        if (!defined('IL_COOKIE_SECURE')) {
            // If this code is executed, we can assume that \ilHTTPS::enableSecureCookies was NOT called before
            // \ilHTTPS::enableSecureCookies already executes session_set_cookie_params()

            $cookie_secure = !$ilSetting->get('https', 0) && $DIC['https']->isDetected();
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

    protected static function initMail(\ILIAS\DI\Container $c): void
    {
        $c["mail.mime.transport.factory"] = static function (\ILIAS\DI\Container $c) {
            return new \ilMailMimeTransportFactory($c->settings(), $c->event());
        };
        $c["mail.mime.sender.factory"] = static function (\ILIAS\DI\Container $c) {
            return new \ilMailMimeSenderFactory($c->settings());
        };
        $c["mail.texttemplates.service"] = static function (\ILIAS\DI\Container $c) {
            return new \ilMailTemplateService(new \ilMailTemplateRepository($c->database()));
        };
    }

    protected static function initCron(\ILIAS\DI\Container $c): void
    {
        $c['cron.repository'] = static function (\ILIAS\DI\Container $c): ilCronJobRepository {
            return new ilCronJobRepositoryImpl(
                $c->database(),
                $c->settings(),
                $c->logger()->cron(),
                $c['component.repository'],
                $c['component.factory']
            );
        };

        $c['cron.manager'] = static function (\ILIAS\DI\Container $c): ilCronManager {
            return new ilCronManagerImpl(
                $c['cron.repository'],
                $c->database(),
                $c->settings(),
                $c->logger()->cron()
            );
        };
    }

    /**
     * @param \ILIAS\DI\Container $c
     */
    protected static function initCustomObjectIcons(\ILIAS\DI\Container $c): void
    {
        $c["object.customicons.factory"] = function ($c) {
            return new ilObjectCustomIconFactory(
                $c->filesystem()->web(),
                $c->upload(),
                $c['ilObjDataCache']
            );
        };
    }

    protected static function initAvatar(\ILIAS\DI\Container $c): void
    {
        $c["user.avatar.factory"] = function ($c) {
            return new \ilUserAvatarFactory($c);
        };
    }

    protected static function initTermsOfService(\ILIAS\DI\Container $c): void
    {
        $c['tos.criteria.type.factory'] = function (
            \ILIAS\DI\Container $c
        ): ilTermsOfServiceCriterionTypeFactoryInterface {
            return new ilTermsOfServiceCriterionTypeFactory(
                $c->rbac()->review(),
                $c['ilObjDataCache'],
                ilCountry::getCountryCodes()
            );
        };

        $c['tos.service'] = function (\ILIAS\DI\Container $c): ilTermsOfServiceHelper {
            $persistence = new ilTermsOfServiceDataGatewayFactory();
            $persistence->setDatabaseAdapter($c->database());
            return new ilTermsOfServiceHelper(
                $persistence,
                $c['tos.document.evaluator'],
                $c['tos.criteria.type.factory'],
                new ilObjTermsOfService()
            );
        };

        $c['tos.document.evaluator'] = function (\ILIAS\DI\Container $c): ilTermsOfServiceDocumentEvaluation {
            return new ilTermsOfServiceSequentialDocumentEvaluation(
                new ilTermsOfServiceLogicalAndDocumentCriteriaEvaluation(
                    $c['tos.criteria.type.factory'],
                    $c->user(),
                    $c->logger()->tos()
                ),
                $c->user(),
                $c->logger()->tos(),
                ilTermsOfServiceDocument::orderBy('sorting')->get()
            );
        };
    }

    protected static function initAccessibilityControlConcept(\ILIAS\DI\Container $c): void
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
     * Used in Soap
     */
    protected static function initSettings(): void
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
        define("ANONYMOUS_USER_ID", (int) $ilSetting->get("anonymous_user_id"));
        define("ANONYMOUS_ROLE_ID", (int) $ilSetting->get("anonymous_role_id"));
        define("SYSTEM_USER_ID", (int) $ilSetting->get("system_user_id"));
        define("SYSTEM_ROLE_ID", (int) $ilSetting->get("system_role_id"));
        define("USER_FOLDER_ID", 7);

        // recovery folder
        define("RECOVERY_FOLDER_ID", (int) $ilSetting->get("recovery_folder_id"));

        // installation id
        define("IL_INST_ID", $ilSetting->get("inst_id", '0'));

        // define default suffix replacements
        define("SUFFIX_REPL_DEFAULT", "php,php3,php4,inc,lang,phtml,htaccess");
        define("SUFFIX_REPL_ADDITIONAL", $ilSetting->get("suffix_repl_additional", ""));

        if (ilContext::usesHTTP()) {
            self::buildHTTPPath();
        }
    }

    /**
     * provide $styleDefinition object
     */
    protected static function initStyle(): void
    {
        global $DIC;
        $component_factory = $DIC["component.factory"];

        // load style definitions
        self::initGlobal(
            "styleDefinition",
            "ilStyleDefinition",
            "./Services/Style/System/classes/class.ilStyleDefinition.php"
        );

        // add user interface hook for style initialisation
        foreach ($component_factory->getActivePluginsInSlot("uihk") as $ui_plugin) {
            $gui_class = $ui_plugin->getUIClassInstance();
            $gui_class->modifyGUI("Services/Init", "init_style", array("styleDefinition" => $DIC->systemStyle()));
        }
    }

    /**
     * Init user with current account id
     */
    public static function initUserAccount(): void
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
    protected static function initLocale(): void
    {
        global $ilSetting;

        if (trim($ilSetting->get("locale")) != "") {
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
            }
        }
    }

    /**
     * go to public section
     */
    public static function goToPublicSection(): void
    {
        global $DIC;

        if (ANONYMOUS_USER_ID == "") {
            self::abortAndDie("Public Section enabled, but no Anonymous user found.");
        }

        $session_destroyed = false;
        if ($DIC['ilAuthSession']->isExpired()) {
            $session_destroyed = true;
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_EXPIRE);
        }
        if (!$DIC['ilAuthSession']->isAuthenticated()) {
            $session_destroyed = true;
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_PUBLIC);
        }

        if ($session_destroyed) {
            $GLOBALS['DIC']['ilAuthSession']->setAuthenticated(true, ANONYMOUS_USER_ID);
        }

        self::initUserAccount();

        $target = '';
        if ($DIC->http()->wrapper()->query()->has('target')) {
            $target = $DIC->http()->wrapper()->query()->retrieve(
                'target',
                $DIC->refinery()->kindlyTo()->string()
            );
        }

        // if target given, try to go there
        if (strlen($target)) {
            // when we are already "inside" goto.php no redirect is needed
            $current_script = substr(strrchr($_SERVER["PHP_SELF"], "/"), 1);
            if ($current_script == "goto.php") {
                return;
            }
            // goto will check if target is accessible or redirect to login
            self::redirect("goto.php?target=" . $_GET["target"]);
        }

        // we do not know if ref_id of request is accesible, so redirecting to root
        self::redirect(
            "ilias.php?baseClass=ilrepositorygui&reloadpublic=1&cmd=&ref_id=" . (defined(
                'ROOT_FOLDER_ID'
            ) ? (string) ROOT_FOLDER_ID : '0')
        );
    }

    /**
     * go to login
     */
    protected static function goToLogin(): void
    {
        global $DIC;

        $a_auth_stat = "";
        ilLoggerFactory::getLogger('init')->debug('Redirecting to login page.');

        if ($DIC['ilAuthSession']->isExpired()) {
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_EXPIRE);
        }
        if (!$DIC['ilAuthSession']->isAuthenticated()) {
            ilSession::setClosingContext(ilSession::SESSION_CLOSE_LOGIN);
        }

        $target = $DIC->http()->wrapper()->query()->has('target')
            ? $DIC->http()->wrapper()->query()->retrieve(
                'target',
                $DIC->refinery()->kindlyTo()->string()
            )
            : '';

        if (strlen($target)) {
            $target = "target=" . $target . "&";
        }

        $client_id = $DIC->http()->wrapper()->cookie()->has('ilClientId')
            ? $DIC->http()->wrapper()->cookie()->retrieve('ilClientId', $DIC->refinery()->kindlyTo()->string())
            : '';

        $script = "login.php?" . $target . "client_id=" . $client_id .
            "&auth_stat=" . $a_auth_stat;

        self::redirect(
            $script,
            "init_error_authentication_fail",
            array(
                "en" => "Authentication failed.",
                "de" => "Authentifizierung fehlgeschlagen."
            )
        );
    }

    /**
     * $lng initialisation
     */
    protected static function initLanguage(bool $a_use_user_language = true): void
    {
        global $DIC;

        /**
         * @var $rbacsystem ilRbacSystem
         */
        global $rbacsystem;

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
    protected static function initAccessHandling(): void
    {
        self::initGlobal(
            "rbacreview",
            "ilRbacReview",
            "./Services/AccessControl/classes/class.ilRbacReview.php"
        );

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
    }

    /**
     * Init log instance
     */
    protected static function initLog(): void
    {
        $log = ilLoggerFactory::getRootLogger();

        self::initGlobal("ilLog", $log);
        // deprecated
        self::initGlobal("log", $log);
    }

    /**
     * Initialize global instance
     * @param string $a_name
     * @param string|object $a_class
     * @param ?string $a_source_file
     */
    protected static function initGlobal($a_name, $a_class, $a_source_file = null): void
    {
        global $DIC;

        $GLOBALS[$a_name] = is_object($a_class) ? $a_class : new $a_class();

        $DIC[$a_name] = function ($c) use ($a_name) {
            return $GLOBALS[$a_name];
        };
    }

    protected static function abortAndDie(string $a_message): void
    {
        if (isset($GLOBALS['ilLog'])) {
            $GLOBALS['ilLog']->write("Fatal Error: ilInitialisation - " . $a_message);
            $GLOBALS['ilLog']->logStack();
        }
        die($a_message);
    }

    /**
     * Prepare developer tools
     */
    protected static function handleDevMode(): void
    {
        if ((defined(SHOWNOTICES) && SHOWNOTICES) || version_compare(PHP_VERSION, '8.0', '>=')) {
            error_reporting(-1);
        }
    }

    protected static bool $already_initialized = false;

    public static function reinitILIAS(): void
    {
        self::$already_initialized = false;
        self::initILIAS();
    }

    /**
     * ilias initialisation
     */
    public static function initILIAS(): void
    {
        if (self::$already_initialized) {
            return;
        }

        $GLOBALS["DIC"] = new Container();
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
        }

        // this MUST happen after everything else is initialized,
        // because this leads to rather unexpected behaviour which
        // is super hard to track down to this.
        self::replaceSuperGlobals($GLOBALS['DIC']);
    }

    /**
     * Init auth session.
     */
    protected static function initSession(): void
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
    public static function handleErrorReporting(): void
    {
        // push the error level as high as possible / sane
        error_reporting(E_ALL & ~E_NOTICE);

        // see handleDevMode() - error reporting might be overwritten again
        // but we need the client ini first
    }

    /**
     * Init core objects (level 0)
     */
    protected static function initCore(): void
    {
        global $ilErr;

        self::handleErrorReporting();

        // breaks CAS: must be included after CAS context isset in AuthUtils
        //self::includePhp5Compliance();

        self::requireCommonIncludes();

        $GLOBALS["DIC"]["ilias.version"] = (new ILIAS\Data\Factory())->version(ILIAS_VERSION_NUMERIC);

        // error handler
        self::initGlobal(
            "ilErr",
            "ilErrorHandling",
            "./Services/Init/classes/class.ilErrorHandling.php"
        );
        PEAR::setErrorHandling(
            PEAR_ERROR_CALLBACK,
            [
                $ilErr, 'errorHandler'
            ]
        );

        self::removeUnsafeCharacters();

        self::initIliasIniFile();

        define('IL_INITIAL_WD', getcwd());

        // deprecated
        self::initGlobal("ilias", "ILIAS", "./Services/Init/classes/class.ilias.php");
    }

    /**
     * Init client-based objects (level 1)
     */
    protected static function initClient(): void
    {
        global $https, $ilias, $DIC;

        self::setCookieConstants();

        self::determineClient();

        self::bootstrapFilesystems();

        self::initResourceStorage();

        self::initClientIniFile();

        // --- needs client ini

        $ilias->client_id = (string) CLIENT_ID;

        if (DEVMODE) {
            self::handleDevMode();
        }

        self::handleMaintenanceMode();

        self::initDatabase();

        self::initComponentService($DIC);

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
                new ilPluginAdmin($DIC["component.repository"]),
                "./Services/Component/classes/class.ilPluginAdmin.php"
            );
        }
        self::initGlobal("https", "ilHTTPS", "./Services/Http/classes/class.ilHTTPS.php");
        self::initSettings();
        self::setSessionHandler();
        self::initMail($GLOBALS['DIC']);
        self::initCron($GLOBALS['DIC']);
        self::initAvatar($GLOBALS['DIC']);
        self::initCustomObjectIcons($GLOBALS['DIC']);
        self::initTermsOfService($GLOBALS['DIC']);
        self::initAccessibilityControlConcept($GLOBALS['DIC']);

        // --- needs settings

        self::initLocale();

        if (ilContext::usesHTTP()) {
            $https->enableSecureCookies();
            $https->checkProtocolAndRedirectIfNeeded();
        }

        // --- object handling

        self::initGlobal(
            "ilObjDataCache",
            "ilObjectDataCache",
            "./Services/Object/classes/class.ilObjectDataCache.php"
        );

        self::initGlobal(
            "objDefinition",
            "ilObjectDefinition",
            "./Services/Object/classes/class.ilObjectDefinition.php"
        );

        // $tree
        $tree = new ilTree(ROOT_FOLDER_ID);
        self::initGlobal("tree", $tree);
        unset($tree);

        self::setSessionCookieParams();
        self::initRefinery($DIC);

        (new InitCtrlService())->init($DIC);

        // Init GlobalScreen
        self::initGlobalScreen($DIC);
    }

    /**
     * Init user / authentification (level 2)
     */
    protected static function initUser(): void
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
    public static function resumeUserSession(): void
    {
        global $DIC;

        if (ilAuthUtils::isAuthenticationForced()) {
            ilAuthUtils::handleForcedAuthentication();
        }

        if (
            !$DIC['ilAuthSession']->isAuthenticated() or
            $DIC['ilAuthSession']->isExpired()
        ) {
            ilLoggerFactory::getLogger('init')->debug('Current session is invalid: ' . $GLOBALS['DIC']['ilAuthSession']->getId());
            $current_script = substr(strrchr($_SERVER["PHP_SELF"], "/"), 1);
            if (self::blockedAuthentication($current_script)) {
                ilLoggerFactory::getLogger('init')->debug('Authentication is started in current script.');
                // nothing todo: authentication is done in current script
                return;
            }

            self::handleAuthenticationFail();
            return;
        }
        // valid session

        self::initUserAccount();
    }

    /**
     * @static
     */
    protected static function handleAuthenticationSuccess(): void
    {
        /**
         * @var $ilUser ilObjUser
         */
        global $ilUser;

        ilOnlineTracking::updateAccess($ilUser);
    }

    /**
     * @static
     */
    protected static function handleAuthenticationFail(): void
    {
        global $DIC;

        ilLoggerFactory::getLogger('init')->debug('Handling of failed authentication.');

        // #10608
        if (
            ilContext::getType() == ilContext::CONTEXT_SOAP ||
            ilContext::getType() == ilContext::CONTEXT_WAC) {
            throw new Exception("Authentication failed.");
        }

        if (($DIC->http()->request()->getQueryParams()['cmdMode'] ?? 0) === 'asynch') {
            $DIC->language()->loadLanguageModule('init');
            $DIC->http()->saveResponse(
                $DIC->http()->response()
                    ->withStatus(403)
                    ->withBody(Streams::ofString($DIC->language()->txt('init_error_authentication_fail')))
            );
            $DIC->http()->sendResponse();
            $DIC->http()->close();
        }
        if (
            $DIC['ilAuthSession']->isExpired() &&
            !\ilObjUser::_isAnonymous($DIC['ilAuthSession']->getUserId())
        ) {
            ilLoggerFactory::getLogger('init')->debug('Expired session found -> redirect to login page');
            self::goToLogin();
            return;
        }
        if (ilPublicSectionSettings::getInstance()->isEnabledForDomain($_SERVER['SERVER_NAME'])) {
            ilLoggerFactory::getLogger('init')->debug('Redirect to public section.');
            self::goToPublicSection();
            return;
        }
        ilLoggerFactory::getLogger('init')->debug('Redirect to login page.');
        self::goToLogin();
    }

    /**
     * @param \ILIAS\DI\Container $container
     */
    protected static function initHTTPServices(\ILIAS\DI\Container $container): void
    {
        $init_http = new InitHttpServices();
        $init_http->init($container);
    }

    /**
     * @param \ILIAS\DI\Container $c
     */
    private static function initGlobalScreen(\ILIAS\DI\Container $c): void
    {
        $c['global_screen'] = function () use ($c) {
            return new Services(new ilGSProviderFactory($c), htmlentities(str_replace(" ", "_", ILIAS_VERSION)));
        };
        $c->globalScreen()->tool()->context()->stack()->clear();
        $c->globalScreen()->tool()->context()->claim()->main();
//        $c->globalScreen()->tool()->context()->current()->addAdditionalData('DEVMODE', (bool) DEVMODE);
    }

    /**
     * init the ILIAS UI framework.
     */
    public static function initUIFramework(\ILIAS\DI\Container $c): void
    {
        $init_ui = new InitUIFramework();
        $init_ui->init($c);

        $component_repository = $c["component.repository"];
        $component_factory = $c["component.factory"];
        foreach ($component_repository->getPlugins() as $pl) {
            if (!$pl->isActive()) {
                continue;
            }
            $plugin = $component_factory->getPlugin($pl->getId());
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
    protected static function initRefinery(\ILIAS\DI\Container $container): void
    {
        $container['refinery'] = function ($container) {
            $dataFactory = new \ILIAS\Data\Factory();
            $language = $container['lng'];

            return new \ILIAS\Refinery\Factory($dataFactory, $language);
        };
    }

    /**
     * @param Container $container
     */
    protected static function replaceSuperGlobals(\ILIAS\DI\Container $container): void
    {
        /** @var ilIniFile $client_ini */
        $client_ini = $container['ilClientIniFile'];

        $replace_super_globals = (
            !$client_ini->variableExists('system', 'prevent_super_global_replacement') ||
            !(bool) $client_ini->readVariable('system', 'prevent_super_global_replacement')
        );

        if ($replace_super_globals) {
            $throwOnValueAssignment = defined('DEVMODE') && DEVMODE;

            $_GET = new SuperGlobalDropInReplacement($container['refinery'], $_GET, $throwOnValueAssignment);
            $_POST = new SuperGlobalDropInReplacement($container['refinery'], $_POST, $throwOnValueAssignment);
            $_COOKIE = new SuperGlobalDropInReplacement($container['refinery'], $_COOKIE, $throwOnValueAssignment);
            $_REQUEST = new SuperGlobalDropInReplacement($container['refinery'], $_REQUEST, $throwOnValueAssignment);
        }
    }

    protected static function initComponentService(\ILIAS\DI\Container $container): void
    {
        $init = new InitComponentService();
        $init->init($container);
    }

    /**
     * init HTML output (level 3)
     */
    protected static function initHTML(): void
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
            $dispatcher = new \ILIAS\Init\StartupSequence\StartUpSequenceDispatcher($DIC);
            $dispatcher->dispatch();
        }

        self::initGlobal(
            "ilNavigationHistory",
            "ilNavigationHistory",
            "Services/Navigation/classes/class.ilNavigationHistory.php"
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
            // set hits per page for all lists using table module
            $_GET['limit'] = (int) $ilUser->getPref('hits_per_page');
            ilSession::set('tbl_limit', $_GET['limit']);

            // the next line makes it impossible to save the offset somehow in a session for
            // a specific table (I tried it for the user administration).
            // its not posssible to distinguish whether it has been set to page 1 (=offset = 0)
            // or not set at all (then we want the last offset, e.g. being used from a session var).
            // So I added the wrapping if statement. Seems to work (hopefully).
            // Alex April 14th 2006
            if (isset($_GET['offset']) && $_GET['offset'] != "") {                            // added April 14th 2006
                $_GET['offset'] = (int) $_GET['offset'];        // old code
            }

            self::initGlobal("lti", "ilLTIViewGUI", "./Services/LTI/classes/class.ilLTIViewGUI.php");
            $GLOBALS["DIC"]["lti"]->init();
            self::initKioskMode($GLOBALS["DIC"]);
        }
    }

    /**
     * Extract current cmd from request
     */
    protected static function getCurrentCmd(): string
    {
        if (!isset($_REQUEST["cmd"])) {
            return '';
        }

        $cmd = $_REQUEST["cmd"];
        if (is_array($cmd)) {
            $keys = array_keys($cmd);

            return array_shift($keys);
        }

        return $cmd;
    }

    /**
     * Block authentication based on current request
     */
    protected static function blockedAuthentication(string $a_current_script): bool
    {
        global $DIC;

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

        $requestBaseClass = strtolower((string) ($_REQUEST['baseClass'] ?? ''));
        if ($requestBaseClass == strtolower(ilStartUpGUI::class)) {
            $requestCmdClass = strtolower((string) ($_REQUEST['cmdClass'] ?? ''));
            if (
                $requestCmdClass == strtolower(ilAccountRegistrationGUI::class) ||
                $requestCmdClass == strtolower(ilPasswordAssistanceGUI::class)
            ) {
                ilLoggerFactory::getLogger('auth')->debug('Blocked authentication for cmdClass: ' . $requestCmdClass);
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

        $target = '';
        if ($DIC->http()->wrapper()->query()->has('target')) {
            // @todo refinery undefind
            $target = $_GET['target'];
        }

        // #12884
        if (
            ($a_current_script == "goto.php" && $target == "impr_0") ||
            $requestBaseClass == strtolower(ilImprintGUI::class)
        ) {
            ilLoggerFactory::getLogger('auth')->debug('Blocked authentication for baseClass: ' . $_GET['baseClass']);
            return true;
        }

        if ($a_current_script == 'goto.php' && in_array($target, array(
                'usr_registration',
                'usr_nameassist',
                'usr_pwassist',
                'usr_agreement'
            ))) {
            ilLoggerFactory::getLogger('auth')->debug('Blocked authentication for goto target: ' . $target);
            return true;
        }
        ilLoggerFactory::getLogger('auth')->debug('Authentication required');
        return false;
    }

    /**
     * Translate message if possible
     */
    protected static function translateMessage(string $a_message_id, array $a_message_static = null): string
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
     */
    protected static function redirect(
        string $a_target,
        string $a_message_id = '',
        array $a_message_static = null
    ): void {
        // #12739
        if (defined("ILIAS_HTTP_PATH") &&
            !stristr($a_target, ILIAS_HTTP_PATH)) {
            $a_target = ILIAS_HTTP_PATH . "/" . $a_target;
        }

        foreach (['ext_uid', 'soap_pw'] as $param) {
            if (false === strpos(
                $a_target,
                $param . '='
            ) && isset($GLOBALS['DIC']->http()->request()->getQueryParams()[$param])) {
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
                          "de" => 'Bitte klicken um fortzufahren.'
                    )
                );
                $mess = $message .
                    '<br /><a href="' . $a_target . '">' . $link . '</a>';
            } // plain text
            else {
                // not much we can do here
                $mess = $message;

                if (!trim($mess)) {
                    $mess = self::translateMessage(
                        "init_error_redirect_info",
                        array("en" => 'Redirect not supported by context.',
                                  "de" => 'Weiterleitungen werden durch Kontext nicht unterstützt.'
                            )
                    ) .
                        ' (' . $a_target . ')';
                }
            }

            self::abortAndDie($mess);
        }
    }

    public static function redirectToStartingPage(string $target = ''): void
    {
        global $DIC;

        // fallback, should never happen
        if ($DIC->user()->getId() === ANONYMOUS_USER_ID) {
            self::goToPublicSection();
            return;
        }

        if (
            $target === '' &&
            $DIC->http()->wrapper()->query()->has('target')
        ) {
            $target = $DIC->http()->wrapper()->query()->retrieve(
                'target',
                $DIC->refinery()->kindlyTo()->string()
            );
        }

        // for password change and incomplete profile
        // see ilDashboardGUI
        if ($target === '') {
            ilLoggerFactory::getLogger('init')->debug('Redirect to default starting page');
            $DIC->ctrl()->redirectToURL(ilUserUtil::getStartingPointAsUrl());
        } else {
            ilLoggerFactory::getLogger('init')->debug('Redirect to target: ' . $target);
            $DIC->ctrl()->redirectToURL("goto.php?target=" . $target);
        }
    }

    private static function initBackgroundTasks(\ILIAS\DI\Container $c): void
    {
        global $ilIliasIniFile;

        $n_of_tasks = $ilIliasIniFile->readVariable("background_tasks", "number_of_concurrent_tasks");
        $sync = $ilIliasIniFile->readVariable("background_tasks", "concurrency");

        $n_of_tasks = $n_of_tasks ?: 5;
        $sync = $sync ?: 'sync'; // The default value is sync.

        $c["bt.task_factory"] = function ($c) {
            return new \ILIAS\BackgroundTasks\Implementation\Tasks\BasicTaskFactory($c["di.injector"]);
        };

        $c["bt.persistence"] = function ($c) {
            return \ILIAS\BackgroundTasks\Implementation\Persistence\BasicPersistence::instance();
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

    private static function initInjector(\ILIAS\DI\Container $c): void
    {
        $c["di.dependency_map"] = function ($c) {
            return new \ILIAS\BackgroundTasks\Dependencies\DependencyMap\BaseDependencyMap();
        };

        $c["di.injector"] = function ($c) {
            return new \ILIAS\BackgroundTasks\Dependencies\Injector($c, $c["di.dependency_map"]);
        };
    }

    private static function initKioskMode(\ILIAS\DI\Container $c): void
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
