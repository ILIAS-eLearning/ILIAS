<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './libs/composer/vendor/autoload.php';
include_once './Services/Logging/classes/public/class.ilLogLevel.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;

/**
 * Logging factory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLoggerFactory
{
    const DEFAULT_FORMAT  = "[%suid%] [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
    
    const ROOT_LOGGER = 'root';
    const COMPONENT_ROOT = 'log_root';
    const SETUP_LOGGER = 'setup';
    
    private static $instance = null;
    
    private $settings = null;
    
    private $enabled = false;
    private $loggers = array();
    
    protected function __construct(ilLoggingSettings $settings)
    {
        $this->settings = $settings;
        $this->enabled = $this->getSettings()->isEnabled();
    }

    /**
     *
     * @return ilLoggerFactory
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            include_once './Services/Logging/classes/class.ilLoggingDBSettings.php';
            $settings = ilLoggingDBSettings::getInstance();
            static::$instance = new ilLoggerFactory($settings);
        }
        return static::$instance;
    }
    
    /**
     * get new instance
     * @param ilLoggingSettings $settings
     * @return \self
     */
    public static function newInstance(ilLoggingSettings $settings)
    {
        return static::$instance = new self($settings);
    }
            
    
    /**
     * Get component logger
     * @see mudules.xml or service.xml
     *
     * @param string $a_component_id
     * @return ilLogger
     */
    public static function getLogger($a_component_id)
    {
        $factory = self::getInstance();
        return $factory->getComponentLogger($a_component_id);
    }
    
    /**
     * The unique root logger has a fixed error level
     * @return ilLogger
     */
    public static function getRootLogger()
    {
        $factory = self::getInstance();
        return $factory->getComponentLogger(self::ROOT_LOGGER);
    }
    
    
    /**
     * Init user specific log options
     * @param type $a_login
     * @return boolean
     */
    public function initUser($a_login)
    {
        if (!$this->getSettings()->isBrowserLogEnabledForUser($a_login)) {
            return true;
        }

        include_once("./Services/Logging/classes/extensions/class.ilLineFormatter.php");

        foreach ($this->loggers as $a_component_id => $logger) {
            if ($this->isConsoleAvailable()) {
                $browser_handler = new BrowserConsoleHandler();
                $browser_handler->setLevel($this->getSettings()->getLevelByComponent($a_component_id));
                $browser_handler->setFormatter(new ilLineFormatter(static::DEFAULT_FORMAT, 'Y-m-d H:i:s.u', true, true));
                $logger->getLogger()->pushHandler($browser_handler);
            }
        }
    }
    
    /**
     * Check if console handler is available
     * @return boolean
     */
    protected function isConsoleAvailable()
    {
        include_once './Services/Context/classes/class.ilContext.php';
        if (ilContext::getType() != ilContext::CONTEXT_WEB) {
            return false;
        }
        if (isset($_GET["cmdMode"]) && $_GET["cmdMode"] == "asynch") {
            return false;
        }
        return true;
    }
    
    /**
     * Get settigns
     * @return ilLoggingSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
    
    /**
     *
     * @return ilComponentLogger[]
     */
    protected function getLoggers()
    {
        return $this->loggers;
    }
    
    /**
     * Get component logger
     * @param type $a_component_id
     * @return \Logger
     */
    public function getComponentLogger($a_component_id)
    {
        if (isset($this->loggers[$a_component_id])) {
            return $this->loggers[$a_component_id];
        }

        $loggerNamePrefix = '';
        if (defined('CLIENT_ID')) {
            $loggerNamePrefix = CLIENT_ID . '_';
        }

        switch ($a_component_id) {
            case 'root':
                $logger = new Logger($loggerNamePrefix . 'root');
                break;
                
            default:
                $logger = new Logger($loggerNamePrefix . $a_component_id);
                break;
                
        }
        
        if (!$this->getSettings()->isEnabled()) {
            $null_handler = new NullHandler();
            $logger->pushHandler($null_handler);
            
            include_once './Services/Logging/classes/class.ilComponentLogger.php';
            return $this->loggers[$a_component_id] = new ilComponentLogger($logger);
        }
        
        
        // standard stream handler
        $stream_handler = new StreamHandler(
            $this->getSettings()->getLogDir() . '/' . $this->getSettings()->getLogFile(),
            true
        );
        
        if ($a_component_id == self::ROOT_LOGGER) {
            $stream_handler->setLevel($this->getSettings()->getLevelByComponent(self::COMPONENT_ROOT));
        } else {
            $stream_handler->setLevel($this->getSettings()->getLevelByComponent($a_component_id));
        }
        
        // format lines
        include_once("./Services/Logging/classes/extensions/class.ilLineFormatter.php");
        $line_formatter = new ilLineFormatter(static::DEFAULT_FORMAT, 'Y-m-d H:i:s.u', true, true);
        $stream_handler->setFormatter($line_formatter);
        
        if ($this->getSettings()->isCacheEnabled()) {
            // add new finger crossed handler
            $finger_crossed_handler = new FingersCrossedHandler(
                $stream_handler,
                new ErrorLevelActivationStrategy($this->getSettings()->getCacheLevel()),
                1000
            );
            $logger->pushHandler($finger_crossed_handler);
        } else {
            $logger->pushHandler($stream_handler);
        }

        if (
            $GLOBALS['DIC']->offsetExists('ilUser') &&
            $GLOBALS['DIC']['ilUser'] instanceof ilObjUser
        ) {
            if ($this->getSettings()->isBrowserLogEnabledForUser($GLOBALS['DIC']->user()->getLogin())) {
                if ($this->isConsoleAvailable()) {
                    $browser_handler = new BrowserConsoleHandler();
                    #$browser_handler->setLevel($this->getSettings()->getLevelByComponent($a_component_id));
                    $browser_handler->setLevel($this->getSettings()->getLevel());
                    $browser_handler->setFormatter($line_formatter);
                    $logger->pushHandler($browser_handler);
                }
            }
        }
        

        // suid log
        $logger->pushProcessor(function ($record) {
            $record['suid'] = substr(session_id(), 0, 5);
            return $record;
        });

        // append trace
        include_once './Services/Logging/classes/extensions/class.ilTraceProcessor.php';
        $logger->pushProcessor(new ilTraceProcessor(ilLogLevel::DEBUG));
        
                
        // register new logger
        include_once './Services/Logging/classes/class.ilComponentLogger.php';
        $this->loggers[$a_component_id] = new ilComponentLogger($logger);
        
        return $this->loggers[$a_component_id];
    }
}
