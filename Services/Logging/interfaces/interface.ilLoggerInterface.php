<?php

/**
 * Component logger with individual log levels by component id
 *
 *
 * @author  Stefan Meyer
 * @version $Id$
 *
 */
interface ilLoggerInterface
{
    /**
     * Check whether current logger is handling a log level
     *
     * @param int $a_level
     * @return bool
     */
    public function isHandling($a_level);
    
    public function log($a_message, $a_level = ilLogLevel::INFO);
    
    public function dump($a_variable, $a_level = ilLogLevel::INFO);
    
    public function debug($a_message, $a_context = []);
    
    public function info($a_message);
    
    public function notice($a_message);
    
    public function warning($a_message);
    
    public function error($a_message);
    
    public function critical($a_message);
    
    public function alert($a_message);
    
    public function emergency($a_message);
    
    /**
     * Get logger instance
     *
     * @return \Logger
     */
    public function getLogger();
    
    /**
     * write log message
     *
     * @deprecated since version 5.1
     * @see        ilLogger->info(), ilLogger()->debug(), ...
     */
    public function write($a_message, $a_level = ilLogLevel::INFO);
    
    /**
     * Write language log
     *
     * @deprecated since version 5.1
     */
    public function writeLanguageLog($a_topic, $a_lang_key);
    
    /**
     * log stack trace
     *
     * @param type $a_level
     * @param type $a_message
     * @throws \Exception
     */
    public function logStack($a_level = null, $a_message = '');
    
    /**
     * Write memory peak usage
     * Automatically called at end of script
     *
     * @param int $a_level
     */
    public function writeMemoryPeakUsage($a_level);
}
