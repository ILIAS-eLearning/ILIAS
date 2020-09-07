<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './libs/composer/vendor/autoload.php';
include_once __DIR__ . '/public/class.ilLogLevel.php';


use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;

/**
 * Component logger with individual log levels by component id
 *
 *
 * @author Stefan Meyer
 * @version $Id$
 *
 */
abstract class ilLogger
{
    private $logger = null;
    
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Check whether current logger is handling a log level
     * @param int $a_level
     * @return bool
     */
    public function isHandling($a_level)
    {
        return $this->getLogger()->isHandling($a_level);
    }
    
    public function log($a_message, $a_level = ilLogLevel::INFO)
    {
        return $this->getLogger()->log($a_level, $a_message);
    }
    
    public function dump($a_variable, $a_level = ilLogLevel::INFO)
    {
        return $this->log(print_r($a_variable, true), $a_level);
    }
    
    public function debug($a_message, $a_context = array())
    {
        return $this->getLogger()->debug($a_message, $a_context);
    }
    
    public function info($a_message)
    {
        return $this->getLogger()->info($a_message);
    }

    public function notice($a_message)
    {
        return $this->getLogger()->notice($a_message);
    }

    public function warning($a_message)
    {
        return $this->getLogger()->warning($a_message);
    }
    
    public function error($a_message)
    {
        return $this->getLogger()->error($a_message);
    }
    
    public function critical($a_message)
    {
        $this->getLogger()->critical($a_message);
    }

    public function alert($a_message)
    {
        return $this->getLogger()->alert($a_message);
    }
    
    
    public function emergency($a_message)
    {
        return $this->getLogger()->emergency($a_message);
    }
    
    /**
     * Get logger instance
     * @return \Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * write log message
     * @deprecated since version 5.1
     * @see ilLogger->info(), ilLogger()->debug(), ...
     */
    public function write($a_message, $a_level = ilLogLevel::INFO)
    {
        include_once './Services/Logging/classes/public/class.ilLogLevel.php';
        if (!in_array($a_level, ilLogLevel::getLevels())) {
            $a_level = ilLogLevel::INFO;
        }
        
        $this->getLogger()->log($a_level, $a_message);
    }

    /**
     * Write language log
     * @deprecated since version 5.1
     */
    public function writeLanguageLog($a_topic, $a_lang_key)
    {
        $this->getLogger()->debug("Language (" . $a_lang_key . "): topic -" . $a_topic . "- not present");
    }
    
    /**
     * log stack trace
     * @param type $a_level
     * @param type $a_message
     * @throws \Exception
     */
    public function logStack($a_level = null, $a_message = '')
    {
        if (is_null($a_level)) {
            $a_level = ilLogLevel::INFO;
        }

        include_once './Services/Logging/classes/public/class.ilLogLevel.php';
        if (!in_array($a_level, ilLogLevel::getLevels())) {
            $a_level = ilLogLevel::INFO;
        }
        
        
        try {
            throw new \Exception($a_message);
        } catch (Exception $ex) {
            $this->getLogger()->log($a_level, $a_message . "\n" . $ex->getTraceAsString());
        }
    }
    
    /**
     * Write memory peak usage
     * Automatically called at end of script
     * @param int $a_level
     */
    public function writeMemoryPeakUsage($a_level)
    {
        $this->getLogger()->pushProcessor(new MemoryPeakUsageProcessor());
        $this->getLogger()->log($a_level, 'Memory usage: ');
        $this->getLogger()->popProcessor();
    }
}
