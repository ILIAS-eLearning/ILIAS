<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */
/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

require_once 'Services/Environment/classes/class.ilRuntime.php';

/**
* Error Handling & global info handling
* uses PEAR error class
*
* @author	Stefan Meyer <meyer@leifos.com>
* @author	Sascha Hofmann <shofmann@databay.de>
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
* @extends PEAR
* @todo		when an error occured and clicking the back button to return to previous page the referer-var in session is deleted -> server error
* @todo		This class is a candidate for a singleton. initHandlers could only be called once per process anyways, as it checks for static $handlers_registered.
*/

require_once("Services/Exceptions/classes/class.ilDelegatingHandler.php");
require_once("Services/Exceptions/classes/class.ilPlainTextHandler.php");
require_once("Services/Exceptions/classes/class.ilTestingHandler.php");

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\CallbackHandler;
use Whoops\Exception\Inspector;

class ilErrorHandling extends PEAR
{
    private const SENSTIVE_PARAMETER_NAMES = [
        'password',
        'passwd',
        'passwd_retype',
        'current_password',
        'usr_password',
        'usr_password_retype',
        'new_password',
        'new_password_retype',
    ];

    /**
    * Toggle debugging on/off
    * @var		boolean
    * @access	private
    */
    public $DEBUG_ENV;

    /**
    * Error level 1: exit application immedietly
    * @var		integer
    * @access	public
    */
    public $FATAL;

    /**
    * Error level 2: show warning page
    * @var		integer
    * @access	public
    */
    public $WARNING;

    /**
    * Error level 3: show message in recent page
    * @var		integer
    * @access	public
    */
    public $MESSAGE;

    /**
     * Are the whoops error handlers already registered?
     * @var bool
     */
    protected static $whoops_handlers_registered = false;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        parent::__construct();

        // init vars
        $this->DEBUG_ENV = true;
        $this->FATAL = 1;
        $this->WARNING = 2;
        $this->MESSAGE = 3;

        $this->error_obj = false;
        
        $this->initWhoopsHandlers();
        
        // somehow we need to get rid of the whoops error handler
        restore_error_handler();
        set_error_handler(array($this, "handlePreWhoops"));
    }
    
    /**
     * Initialize Error and Exception Handlers.
     *
     * Initializes Whoops, a logging handler and a delegate handler for the late initialisation
     * of an appropriate error handler.
     *
     * @return void
     */
    protected function initWhoopsHandlers()
    {
        if (self::$whoops_handlers_registered) {
            // Only register whoops error handlers once.
            return;
        }
        
        $ilRuntime = $this->getIlRuntime();
        $this->whoops = $this->getWhoops();
        
        $this->whoops->pushHandler(new ilDelegatingHandler($this));
        
        if ($ilRuntime->shouldLogErrors()) {
            $this->whoops->pushHandler($this->loggingHandler());
        }
        
        $this->whoops->register();
        
        self::$whoops_handlers_registered = true;
    }

    /**
     * Get a handler for an error or exception.
     *
     * Uses Whoops Pretty Page Handler in DEVMODE and the legacy ILIAS-Error handlers otherwise.
     *
     * @return Whoops\Handler
     */
    public function getHandler()
    {
        // TODO: * Use Whoops in production mode? This would require an appropriate
        //		   error-handler.
        //		 * Check for context? The current implementation e.g. would output HTML for
        //		   for SOAP.

        if ($this->isDevmodeActive()) {
            return $this->devmodeHandler();
        }

        return $this->defaultHandler();
    }

    public function getLastError()
    {
        return $this->error_obj;
    }

    /**
    * defines what has to happen in case of error
    * @access	private
    * @param	object	Error
    */
    public function errorHandler($a_error_obj)
    {
        global $log;

        // see bug 18499 (some calls to raiseError do not pass a code, which leads to security issues, if these calls
        // are done due to permission checks)
        if ($a_error_obj->getCode() == null) {
            $a_error_obj->code = $this->WARNING;
        }

        $this->error_obj = &$a_error_obj;
        //echo "-".$_SESSION["referer"]."-";
        if ($_SESSION["failure"] && substr($a_error_obj->getMessage(), 0, 22) != "Cannot find this block") {
            $m = "Fatal Error: Called raise error two times.<br>" .
                "First error: " . $_SESSION["failure"] . '<br>' .
                "Last Error:" . $a_error_obj->getMessage();
            //return;
            $log->write($m);
            #$log->writeWarning($m);
            #$log->logError($a_error_obj->getCode(), $m);
            unset($_SESSION["failure"]);
            die($m);
        }

        if (substr($a_error_obj->getMessage(), 0, 22) == "Cannot find this block") {
            if (DEVMODE == 1) {
                echo "<b>DEVMODE</b><br><br>";
                echo "<b>Template Block not found.</b><br>";
                echo "You used a template block in your code that is not available.<br>";
                echo "Native Messge: <b>" . $a_error_obj->getMessage() . "</b><br>";
                if (is_array($a_error_obj->backtrace)) {
                    echo "Backtrace:<br>";
                    foreach ($a_error_obj->backtrace as $b) {
                        if ($b["function"] == "setCurrentBlock" &&
                            basename($b["file"]) != "class.ilTemplate.php") {
                            echo "<b>";
                        }
                        echo "File: " . $b["file"] . ", ";
                        echo "Line: " . $b["line"] . ", ";
                        echo $b["function"] . "()<br>";
                        if ($b["function"] == "setCurrentBlock" &&
                            basename($b["file"]) != "class.ilTemplate.php") {
                            echo "</b>";
                        }
                    }
                }
                exit;
            }
            return;
        }

        if (is_object($log) and $log->enabled == true) {
            $log->write($a_error_obj->getMessage());
            #$log->logError($a_error_obj->getCode(),$a_error_obj->getMessage());
        }

        //echo $a_error_obj->getCode().":"; exit;
        if ($a_error_obj->getCode() == $this->FATAL) {
            trigger_error(stripslashes($a_error_obj->getMessage()), E_USER_ERROR);
            exit();
        }

        if ($a_error_obj->getCode() == $this->WARNING) {
            if ($this->DEBUG_ENV) {
                $message = $a_error_obj->getMessage();
            } else {
                $message = "Under Construction";
            }

            $_SESSION["failure"] = $message;

            if (!defined("ILIAS_MODULE")) {
                ilUtil::redirect("error.php");
            } else {
                ilUtil::redirect("../error.php");
            }
        }

        if ($a_error_obj->getCode() == $this->MESSAGE) {
            $_SESSION["failure"] = $a_error_obj->getMessage();
            // save post vars to session in case of error
            $_SESSION["error_post_vars"] = $_POST;

            if (empty($_SESSION["referer"])) {
                $dirname = dirname($_SERVER["PHP_SELF"]);
                $ilurl = parse_url(ILIAS_HTTP_PATH);

                $subdir = '';
                if (is_array($ilurl) && array_key_exists('path', $ilurl) && strlen($ilurl['path'])) {
                    $subdir = substr(strstr($dirname, (string) $ilurl["path"]), strlen((string) $ilurl["path"]));
                    $updir = "";
                }
                if ($subdir) {
                    $num_subdirs = substr_count($subdir, "/");

                    for ($i = 1;$i <= $num_subdirs;$i++) {
                        $updir .= "../";
                    }
                }
                ilUtil::redirect($updir . "index.php");
            }

            /* #12104
            check if already GET-Parameters exists in Referer-URI
            if (substr($_SESSION["referer"],-4) == ".php")
            {
                $glue = "?";
            }
            else
            {
                // this did break permanent links (".html&")
                $glue = "&";
            }
            */
            ilUtil::redirect($_SESSION["referer"]);
        }
    }

    public function getMessage()
    {
        return $this->message;
    }
    public function setMessage($a_message)
    {
        $this->message = $a_message;
    }
    public function appendMessage($a_message)
    {
        if ($this->getMessage()) {
            $this->message .= "<br /> ";
        }
        $this->message .= $a_message;
    }
    
    /**
     * This is used in Soap calls to write PHP error in ILIAS Logfile
     * Not used yet!!!
     *
     * @access public
     * @static
     *
     * @param
     */
    public static function _ilErrorWriter($errno, $errstr, $errfile, $errline)
    {
        global $ilLog;
        
        switch ($errno) {
            case E_USER_ERROR:
                $ilLog->write('PHP errror: ' . $errstr . '. FATAL error on line ' . $errline . ' in file ' . $errfile);
                unset($ilLog);
                exit(1);
            
            case E_USER_WARNING:
                $ilLog->write('PHP warning: [' . $errno . '] ' . $errstr . ' on line ' . $errline . ' in file ' . $errfile);
                break;
            
        }
        return true;
    }
    
    /**
     * Get ilRuntime.
     * @return ilRuntime
     */
    protected function getIlRuntime()
    {
        return ilRuntime::getInstance();
    }
    
    /**
     * Get an instance of Whoops/Run.
     * @return Whoops\Run
     */
    protected function getWhoops()
    {
        return new Run();
    }
    
    /**
     * Is the DEVMODE switched on?
     * @return bool
     */
    protected function isDevmodeActive()
    {
        return defined("DEVMODE") && (int) DEVMODE === 1;
    }

    /**
     * Get a default error handler.
     * @return Whoops\Handler
     */
    protected function defaultHandler()
    {
        // php7-todo : alex, 1.3.2016: Exception -> Throwable, please check
        return new CallbackHandler(function ($exception, Inspector $inspector, Run $run) {
            global $lng;

            require_once("Services/Logging/classes/error/class.ilLoggingErrorSettings.php");
            require_once("Services/Logging/classes/error/class.ilLoggingErrorFileStorage.php");
            require_once("Services/Utilities/classes/class.ilUtil.php");

            $session_id = substr(session_id(), 0, 5);
            $random = new \ilRandom();
            $err_num = $random->int(1, 9999);
            $file_name = $session_id . "_" . $err_num;

            $logger = ilLoggingErrorSettings::getInstance();
            if (!empty($logger->folder())) {
                $lwriter = new ilLoggingErrorFileStorage($inspector, $logger->folder(), $file_name);
                $lwriter = $lwriter->withExclusionList(self::SENSTIVE_PARAMETER_NAMES);
                $lwriter->write();
            }

            //Use $lng if defined or fallback to english
            if ($lng !== null) {
                $lng->loadLanguageModule('logging');
                $message = sprintf($lng->txt("log_error_message"), $file_name);

                if ($logger->mail()) {
                    $message .= " " . sprintf($lng->txt("log_error_message_send_mail"), $logger->mail(), $file_name, $logger->mail());
                }
            } else {
                $message = 'Sorry, an error occured. A logfile has been created which can be identified via the code "' . $file_name . '"';

                if ($logger->mail()) {
                    $message .= ' ' . 'Please send a mail to <a href="mailto:' . $logger->mail() . '?subject=code: ' . $file_name . '">' . $logger->mail() . '</a>';
                }
            }

            ilUtil::sendFailure($message, true);
            ilUtil::redirect("error.php");
        });
    }

    /**
     * Get the handler to be used in DEVMODE.
     * @return Whoops\Handler\HandlerInterface
     */
    protected function devmodeHandler()
    {
        global $ilLog;
        
        switch (ERROR_HANDLER) {
            case "TESTING":
                return (new ilTestingHandler())->withExclusionList(self::SENSTIVE_PARAMETER_NAMES);
            case "PLAIN_TEXT":
                return (new ilPlainTextHandler())->withExclusionList(self::SENSTIVE_PARAMETER_NAMES);
            case "PRETTY_PAGE":
                // fallthrough
            default:
                if ((!defined('ERROR_HANDLER') || ERROR_HANDLER != 'PRETTY_PAGE') && $ilLog) {
                    $ilLog->write(
                        "Unknown or undefined error handler '" . ERROR_HANDLER . "'. " .
                        "Falling back to PrettyPageHandler."
                    );
                }

                $prettyPageHandler = new PrettyPageHandler();

                $this->addEditorSupport($prettyPageHandler);

                foreach (self::SENSTIVE_PARAMETER_NAMES as $param) {
                    $prettyPageHandler->blacklist('_POST', $param);
                }

                return $prettyPageHandler;
        }
    }

    /**
     * @param PrettyPageHandler $handler
     */
    protected function addEditorSupport(PrettyPageHandler $handler)
    {
        $editorUrl = defined('ERROR_EDITOR_URL') ? ERROR_EDITOR_URL : '';
        if (!is_string($editorUrl) || 0 === strlen($editorUrl)) {
            return;
        }

        $pathTranslationConfig = defined('ERROR_EDITOR_PATH_TRANSLATIONS') ? ERROR_EDITOR_PATH_TRANSLATIONS : '';

        $pathTranslations = $this->parseEditorPathTranslation($pathTranslationConfig);

        $handler->setEditor(function ($file, $line) use ($editorUrl, $pathTranslations) {
            $this->applyEditorPathTranslations($file, $pathTranslations);

            return str_ireplace(
                ['[FILE]', '[LINE]'],
                [$file, $line],
                $editorUrl
            );
        });
    }

    /**
     * @param string $file
     * @param array $pathTranslations
     */
    protected function applyEditorPathTranslations(string &$file, array $pathTranslations)
    {
        foreach ($pathTranslations as $from => $to) {
            $file = preg_replace('@' . $from . '@', $to, $file);
        }
    }


    /**
     * @param string $pathTranslationConfig
     * @return array
     */
    protected function parseEditorPathTranslation(string $pathTranslationConfig)
    {
        $pathTranslations = [];

        $mappings = explode('|', $pathTranslationConfig);
        foreach ($mappings as $mapping) {
            $parts = explode(',', $mapping);
            $pathTranslations[trim($parts[0])] = trim($parts[1]);
        }

        return $pathTranslations;
    }
    
    /**
     * Get the handler to be used to log errors.
     * @return Whoops\Handler
     */
    protected function loggingHandler()
    {
        // php7-todo : alex, 1.3.2016: Exception -> Throwable, please check
        return new CallbackHandler(function ($exception, Inspector $inspector, Run $run) {
            /**
             * Don't move this out of this callable
             * @var ilLog $ilLog;
             */
            global $ilLog;

            if (is_object($ilLog)) {
                $message = $exception->getMessage() . ' in ' . $exception->getFile() . ":" . $exception->getLine() ;
                $message .= $exception->getTraceAsString();
                $ilLog->error($exception->getCode() . ' ' . $message);
            }
            
            // Send to system logger
            error_log($exception->getMessage());
        });
    }
    
    public function handlePreWhoops($level, $message, $file, $line)
    {
        global $ilLog;
        
        if ($level & error_reporting()) {
            
            // correct-with-php5-removal JL start
            // ignore all E_STRICT that are E_NOTICE (or nothing at all) in PHP7
            if (version_compare(PHP_VERSION, '7.0.0', '<')) {
                if ($level == E_STRICT) {
                    if (!stristr($message, "should be compatible") &&
                        !stristr($message, "should not be called statically") &&
                        !stristr($message, "should not be abstract")) {
                        return true;
                    };
                }
            }
            // correct-with-php5-removal end

            if (!$this->isDevmodeActive()) {
                // log E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED only
                if ($level >= E_USER_NOTICE) {
                    if ($ilLog) {
                        $severity = Whoops\Util\Misc::TranslateErrorCode($level);
                        $ilLog->write("\n\n" . $severity . " - " . $message . "\n" . $file . " - line " . $line . "\n");
                    }
                    return true;
                }
            }
            
            // trigger whoops error handling
            if ($this->whoops) {
                return $this->whoops->handleError($level, $message, $file, $line);
            }
        }
        
        return false;
    }
} // END class.ilErrorHandling
