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

use Whoops\Run;
use Whoops\RunInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\CallbackHandler;
use Whoops\Exception\Inspector;
use Whoops\Handler\HandlerInterface;

/**
 * Error Handling & global info handling
 * uses PEAR error class
 * @author      Stefan Meyer <meyer@leifos.com>
 * @author      Sascha Hofmann <shofmann@databay.de>
 * @author      Richard Klees <richard.klees@concepts-and-training.de>
 * @author      Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version     $Id$
 * @extends     PEAR
 * @todo        when an error occured and clicking the back button to return to previous page the referer-var in session is deleted -> server error
 * @todo        This class is a candidate for a singleton. initHandlers could only be called once per process anyways, as it checks for static $handlers_registered.
 */
class ilErrorHandling extends PEAR
{
    protected ?RunInterface $whoops;

    protected string $message;
    protected bool $DEBUG_ENV;

    /**
     * Error level 1: exit application immedietly
     */
    public int $FATAL = 1;

    /**
     * Error level 2: show warning page
     */
    public int $WARNING = 2;

    /**
     * Error level 3: show message in recent page
     */
    public int $MESSAGE = 3;

    /**
     * Are the whoops error handlers already registered?
     * @var bool
     */
    protected static bool $whoops_handlers_registered = false;

    /**
     * @var ?PEAR_Error error obj
     */
    protected $error_obj = null;

    /**
     * Constructor
     * @access    public
     */
    public function __construct()
    {
        parent::__construct();

        // init vars
        $this->DEBUG_ENV = true;
        $this->FATAL = 1;
        $this->WARNING = 2;
        $this->MESSAGE = 3;

        $this->error_obj = null;

        $this->initWhoopsHandlers();

        // somehow we need to get rid of the whoops error handler
        restore_error_handler();
        set_error_handler([$this, "handlePreWhoops"]);
    }

    /**
     * Initialize Error and Exception Handlers.
     * Initializes Whoops, a logging handler and a delegate handler for the late initialisation
     * of an appropriate error handler.
     */
    protected function initWhoopsHandlers(): void
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
     * Uses Whoops Pretty Page Handler in DEVMODE and the legacy ILIAS-Error handlers otherwise.
     */
    public function getHandler(): HandlerInterface
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

    /**
     * Defines what has to happen in case of error
     * @param PEAR_Error $a_error_obj PEAR Error object
     */
    public function errorHandler($a_error_obj): void
    {
        global $log;

        // see bug 18499 (some calls to raiseError do not pass a code, which leads to security issues, if these calls
        // are done due to permission checks)
        if ($a_error_obj->getCode() == null) {
            $a_error_obj->code = $this->WARNING;
        }

        $this->error_obj = &$a_error_obj;
        //echo "-".$_SESSION["referer"]."-";
        $session_failure = ilSession::get('failure');
        if ($session_failure && strpos($a_error_obj->getMessage(), "Cannot find this block") !== 0) {
            $m = "Fatal Error: Called raise error two times.<br>" .
                "First error: " . $session_failure . '<br>' .
                "Last Error:" . $a_error_obj->getMessage();
            //return;
            $log->write($m);
            #$log->writeWarning($m);
            #$log->logError($a_error_obj->getCode(), $m);
            ilSession::clear('failure');
            die($m);
        }

        if (strpos($a_error_obj->getMessage(), "Cannot find this block") === 0) {
            if (DEVMODE == 1) {
                echo "<b>DEVMODE</b><br><br>";
                echo "<b>Template Block not found.</b><br>";
                echo "You used a template block in your code that is not available.<br>";
                echo "Native Messge: <b>" . $a_error_obj->getMessage() . "</b><br>";
                if (is_array($a_error_obj->backtrace)) {
                    echo "Backtrace:<br>";
                    foreach ($a_error_obj->backtrace as $b) {
                        if ($b["function"] === "setCurrentBlock" &&
                            basename($b["file"]) !== "class.ilTemplate.php") {
                            echo "<b>";
                        }
                        echo "File: " . $b["file"] . ", ";
                        echo "Line: " . $b["line"] . ", ";
                        echo $b["function"] . "()<br>";
                        if ($b["function"] === "setCurrentBlock" &&
                            basename($b["file"]) !== "class.ilTemplate.php") {
                            echo "</b>";
                        }
                    }
                }
                exit;
            }
            return;
        }

        if ($log instanceof ilLogger) {
            $log->write($a_error_obj->getMessage());
        }
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

            ilSession::set('failure', $message);

            if (!defined("ILIAS_MODULE")) {
                ilUtil::redirect("error.php");
            } else {
                ilUtil::redirect("../error.php");
            }
        }
        $updir = '';
        if ($a_error_obj->getCode() == $this->MESSAGE) {
            ilSession::set('failure', $a_error_obj->getMessage());
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

                    for ($i = 1; $i <= $num_subdirs; $i++) {
                        $updir .= "../";
                    }
                }
                ilUtil::redirect($updir . "index.php");
            }
            ilUtil::redirect($_SESSION["referer"]);
        }
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $a_message): void
    {
        $this->message = $a_message;
    }

    public function appendMessage(string $a_message): void
    {
        if ($this->getMessage()) {
            $this->message .= "<br /> ";
        }
        $this->message .= $a_message;
    }

    protected function getIlRuntime(): ilRuntime
    {
        return ilRuntime::getInstance();
    }

    protected function getWhoops(): RunInterface
    {
        return new Run();
    }

    protected function isDevmodeActive(): bool
    {
        return defined("DEVMODE") && (int) DEVMODE === 1;
    }

    protected function defaultHandler(): HandlerInterface
    {
        // php7-todo : alex, 1.3.2016: Exception -> Throwable, please check
        return new CallbackHandler(function ($exception, Inspector $inspector, Run $run) {
            global $DIC;

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
                $lwriter->write();
            }

            //Use $lng if defined or fallback to english
            if ($DIC->isDependencyAvailable('language')) {
                $DIC->language()->loadLanguageModule('logging');
                $message = sprintf($DIC->language()->txt("log_error_message"), $file_name);

                if ($logger->mail()) {
                    $message .= " " . sprintf(
                        $DIC->language()->txt("log_error_message_send_mail"),
                        $logger->mail(),
                        $file_name,
                        $logger->mail()
                    );
                }
            } else {
                $message = 'Sorry, an error occured. A logfile has been created which can be identified via the code "' . $file_name . '"';

                if ($logger->mail()) {
                    $message .= ' ' . 'Please send a mail to <a href="mailto:' . $logger->mail() . '?subject=code: ' . $file_name . '">' . $logger->mail() . '</a>';
                }
            }
            if ($DIC->isDependencyAvailable('ui') && $DIC->isDependencyAvailable('ctrl')) {
                $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', $message, true);
                $DIC->ctrl()->redirectToURL("error.php");
            } else {
                ilSession::set('failure', $message);
                header("Location: error.php");
                exit;
            }
        });
    }

    /**
     * Get the handler to be used in DEVMODE.
     */
    protected function devmodeHandler(): HandlerInterface
    {
        global $ilLog;

        switch (ERROR_HANDLER) {
            case "TESTING":
                return new ilTestingHandler();

            case "PLAIN_TEXT":
                return new ilPlainTextHandler();

            case "PRETTY_PAGE":
                // fallthrough
            default:
                if ((!defined('ERROR_HANDLER') || ERROR_HANDLER !== 'PRETTY_PAGE') && $ilLog) {
                    $ilLog->write(
                        "Unknown or undefined error handler '" . ERROR_HANDLER . "'. " .
                        "Falling back to PrettyPageHandler."
                    );
                }

                $prettyPageHandler = new PrettyPageHandler();

                $this->addEditorSupport($prettyPageHandler);

                return $prettyPageHandler;
        }
    }

    protected function addEditorSupport(PrettyPageHandler $handler): void
    {
        $editorUrl = defined('ERROR_EDITOR_URL') ? ERROR_EDITOR_URL : '';
        if (!is_string($editorUrl) || $editorUrl === '') {
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

    protected function applyEditorPathTranslations(string &$file, array $pathTranslations): void
    {
        foreach ($pathTranslations as $from => $to) {
            $file = preg_replace('@' . $from . '@', $to, $file);
        }
    }

    protected function parseEditorPathTranslation(string $pathTranslationConfig): array
    {
        $pathTranslations = [];

        $mappings = explode('|', $pathTranslationConfig);
        foreach ($mappings as $mapping) {
            $parts = explode(',', $mapping);
            $pathTranslations[trim($parts[0])] = trim($parts[1]);
        }

        return $pathTranslations;
    }

    protected function loggingHandler(): HandlerInterface
    {
        // php7-todo : alex, 1.3.2016: Exception -> Throwable, please check
        return new CallbackHandler(function ($exception, Inspector $inspector, Run $run) {
            /**
             * Don't move this out of this callable
             * @var ilLogger $ilLog ;
             */
            global $ilLog;

            if (is_object($ilLog)) {
                $message = $exception->getMessage() . ' in ' . $exception->getFile() . ":" . $exception->getLine();
                $message .= $exception->getTraceAsString();
                $ilLog->error($exception->getCode() . ' ' . $message);
            }

            // Send to system logger
            error_log($exception->getMessage());
        });
    }

    /**
     * Parameter types according to PHP doc: set_error_handler
     * @throws \Whoops\Exception\ErrorException
     */
    public function handlePreWhoops(int $level, string $message, string $file, int $line): bool
    {
        global $ilLog;

        if ($level & error_reporting()) {
            if (!$this->isDevmodeActive()) {
                // log E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED only
                if ($level >= E_USER_NOTICE) {
                    if ($ilLog) {
                        $severity = Whoops\Util\Misc::translateErrorCode($level);
                        $ilLog->write("\n\n" . $severity . " - " . $message . "\n" . $file . " - line " . $line . "\n");
                    }
                    return true;
                }
            }

            // trigger whoops error handling
            if ($this->whoops instanceof RunInterface) {
                return $this->whoops->handleError($level, $message, $file, $line);
            }
            if ($this->whoops) {
                return $this->whoops->handleError($level, $message, $file, $line);
            }
        }
        return true;
    }
}
